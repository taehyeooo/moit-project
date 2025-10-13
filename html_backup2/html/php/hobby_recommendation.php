<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$site_title = "MOIT - 취미 추천";
$error_message = '';
$recommendations = [];
$stats = [
    'totalMeetings' => 0,
    'popularCategory' => 'N/A',
    'newUsersThisWeek' => 0
];

// 데이터베이스 연결
try {
    $pdo = getDBConnection();

    // 통계 데이터 가져오기
    $stats['totalMeetings'] = $pdo->query("SELECT COUNT(*) FROM meetings")->fetchColumn();
    $popular_category_query = $pdo->query("SELECT category, COUNT(*) as count FROM meetings GROUP BY category ORDER BY count DESC LIMIT 1");
    if ($popular_category_query) {
        $popular_category_result = $popular_category_query->fetch(PDO::FETCH_ASSOC);
        $stats['popularCategory'] = $popular_category_result ? $popular_category_result['category'] : 'N/A';
    }
    $stats['newUsersThisWeek'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();

} catch (PDOException $e) {
    $error_message = '데이터를 불러오는 중 오류가 발생했습니다.';
}

// 설문 제출 처리 (기존 로직 유지)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_survey'])) {
    try {
        $survey_data = $_POST;
        unset($survey_data['submit_survey']);

        $request_payload = [
            'user_input' => [
                'survey' => $survey_data,
                'user_context' => ['user_id' => $_SESSION['user_id']]
            ]
        ];

        $ch = curl_init('http://127.0.0.1:8000/agent/invoke');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response_body = curl_exec($ch);
        if (curl_errno($ch)) throw new Exception("AI 추천 서버 통신 실패: " . curl_error($ch));
        curl_close($ch);

        $response_data = json_decode($response_body, true);
        if (isset($response_data['final_answer'])) {
            $json_part = substr($response_data['final_answer'], strpos($response_data['final_answer'], '['));
            if ($json_part) {
                $parsed_recos = json_decode($json_part, true);
                if (is_array($parsed_recos)) {
                    $recommendations = array_map(function($reco) {
                        return [
                            'name' => $reco['name_ko'] ?? '이름 없음',
                            'description' => $reco['short_desc'] ?? '설명 없음',
                            'score' => $reco['score_total'] ?? 0.5,
                            'id' => $reco['hobby_id'] ?? 0,
                            'reason' => $reco['reason'] ?? ''
                        ];
                    }, $parsed_recos);
                }
            }
        }
        if (empty($recommendations)) $error_message = "AI가 추천을 생성하지 못했거나, 응답을 처리하는 데 실패했습니다.";

    } catch (Exception $e) {
        $error_message = '추천 생성 중 오류: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/hobby_recommendation-style.css">
    <!-- 새 디자인의 CSS 추가 -->
    <link rel="stylesheet" href="../assets/index-DKv234qG.css">
</head>
<body>
    <?php require_once 'navbar.php'; ?>

    <main class="main-container">
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="content-wrapper">
            <!-- 왼쪽: 기존 설문조사 또는 추천 결과 (원본 유지) -->
            <div class="left-section">
                <?php if (empty($recommendations)): ?>
                    <div class="survey-container">
                        <h2>당신의 취향을 알려주세요</h2>
                        <p class="survey-subtitle">15개 질문으로 딱 맞는 취미를 찾아드릴게요!</p>
                        <div class="mb-8" style="margin-top: 30px;">
                            <div class="flex justify-between text-sm text-gray-500 mb-2">
                                <span>진행률</span>
                                <span id="progressText">1 / 15</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" id="progressFill" style="width: 6.66%;"></div>
                            </div>
                        </div>
                        <form method="POST" class="survey-form" id="surveyForm">
                            <?php
                                $part1_questions = [
                                    ['name' => 'age_group', 'label' => '1. 연령대를 선택해 주세요.', 'options' => ['10대', '20대', '30대', '40대', '50대 이상']],
                                    ['name' => 'gender', 'label' => '2. 성별을 선택해 주세요.', 'options' => ['남성', '여성', '선택 안 함']],
                                    ['name' => 'occupation', 'label' => '3. 현재 어떤 일을 하고 계신가요?', 'options' => ['학생', '직장인', '프리랜서', '주부', '구직자', '기타']],
                                    ['name' => 'weekly_time', 'label' => '4. 일주일에 온전히 나를 위해 사용할 수 있는 시간은 어느 정도인가요?', 'options' => ['3시간 미만', '3~5시간', '5~10시간', '10시간 이상']],
                                    ['name' => 'monthly_budget', 'label' => '5. 한 달에 취미 활동을 위해 얼마까지 지출할 수 있나요?', 'options' => ['5만원 미만', '5~10만원', '10~20만원', '20만원 이상']],
                                ];
                                $part2_questions = [
                                    ['name' => 'Q6', 'label' => '6. 새로운 사람들과 어울리기보다, 혼자 또는 가까운 친구와 깊이 있는 시간을 보내는 것을 선호합니다.'],
                                    ['name' => 'Q7', 'label' => '7. 반복적인 일상에 안정감을 느끼기보다, 예측 불가능한 새로운 경험을 통해 영감을 얻는 편입니다.'],
                                    ['name' => 'Q8', 'label' => '8. 즉흥적으로 행동하기보다, 명확한 목표를 세우고 계획에 따라 꾸준히 실행하는 것에서 성취감을 느낍니다.'],
                                    ['name' => 'Q9', 'label' => '9. 정해진 규칙을 따르기보다, 나만의 방식과 스타일을 더해 독창적인 결과물을 만드는 것을 즐깁니다.'],
                                    ['name' => 'Q10', 'label' => '10. 과정 자체를 즐기는 것도 좋지만, 꾸준한 연습을 통해 실력이 향상되는 것을 눈으로 확인할 때 가장 큰 보람을 느낍니다.'],
                                    ['name' => 'Q11', 'label' => '11. 하루의 스트레스를 조용히 생각하며 풀기보다, 몸을 움직여 땀을 흘리며 해소하는 것을 선호합니다.'],
                                    ['name' => 'Q12', 'label' => '12. 취미 활동을 통해 새로운 수익을 창출하거나, SNS에서 영향력을 키우는 것에 관심이 많습니다.'],
                                    ['name' => 'Q13', 'label' => '13. 오프라인에서 직접 만나 교류하는 것만큼, 온라인 커뮤니티에서 소통하는 것에서도 강한 소속감을 느낍니다.'],
                                    ['name' => 'Q14', 'label' => '14. 하나의 취미를 깊게 파고드는 전문가가 되기보다, 다양한 분야를 경험해보는 제너럴리스트가 되고 싶습니다.'],
                                    ['name' => 'Q15', 'label' => '15. 이 취미를 통해 \'무엇을 얻을 수 있는가\'보다 \'그 순간이 얼마나 즐거운가\'가 더 중요합니다.'],
                                ];
                                $all_questions = array_merge(
                                    array_map(fn($q) => array_merge($q, ['type' => 'radio']), $part1_questions),
                                    array_map(fn($q) => array_merge($q, ['type' => 'likert']), $part2_questions)
                                );
                            ?>
                            <div id="part1-header" class="survey-part-header" style="display: none;"><h3>Part 1. 기본 정보 설정하기</h3><p class="part-subtitle">추천의 정확도를 높이기 위한 기본적인 정보예요.</p></div>
                            <div id="part2-header" class="survey-part-header" style="display: none;"><h3>Part 2. 당신의 스타일 알아보기</h3><p class="part-subtitle">정답은 없으니, 가장 가깝다고 생각하는 곳에 편하게 체크해 주세요.</p></div>
                            <?php foreach ($all_questions as $index => $q): ?>
                                <div class="question-step <?php echo $index === 0 ? 'active' : ''; ?>" data-step="<?php echo $index + 1; ?>">
                                    <?php if ($q['type'] === 'radio'): ?>
                                        <div class="question-group">
                                            <label class="question-label"><?php echo $q['label']; ?></label>
                                            <div class="option-group-inline">
                                                <?php foreach ($q['options'] as $opt): ?><label class="option-label-inline"><input type="radio" name="<?php echo $q['name']; ?>" value="<?php echo $opt; ?>" required><span><?php echo $opt; ?></span></label><?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php elseif ($q['type'] === 'likert'): ?>
                                        <div class="question-group-likert">
                                            <label class="question-label-likert"><?php echo $q['label']; ?></label>
                                            <div class="likert-scale">
                                                <div class="likert-options">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?><label class="likert-option"><input type="radio" name="<?php echo $q['name']; ?>" value="<?php echo $i; ?>" required><span class="likert-radio-button"><?php echo $i; ?></span></label><?php endfor; ?>
                                                </div>
                                                <div class="likert-labels"><span>전혀 그렇지 않다</span><span>매우 그렇다</span></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="survey-buttons">
                                <button type="button" class="btn-prev" id="prevBtn" style="display: none;">이전</button>
                                <button type="button" class="btn-next" id="nextBtn">다음</button>
                                <button type="submit" name="submit_survey" class="submit-btn" id="submitBtn" style="display: none;">취미 추천받기</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="recommendations-container">
                        <h2>🎉 맞춤 취미 추천</h2>
                        <p class="recommendations-subtitle">설문 결과를 바탕으로 <?php echo count($recommendations); ?>개의 취미를 추천해드려요!</p>
                        <div class="hobby-cards">
                            <?php foreach ($recommendations as $hobby): ?>
                                <div class="hobby-card">
                                    <div class="hobby-card-header"><h3 class="hobby-name"><?php echo htmlspecialchars($hobby['name']); ?></h3></div>
                                    <p class="hobby-description"><?php echo htmlspecialchars($hobby['description']); ?></p>
                                    <div class="hobby-tags">
                                        <?php $reasons = explode(' · ', $hobby['reason']);
                                            foreach (array_filter($reasons) as $reason_tag): ?><span class="tag"><?php echo htmlspecialchars($reason_tag); ?></span><?php endforeach; ?>
                                    </div>
                                    <div class="hobby-score"><span>추천도: <?php echo round($hobby['score']); ?>%</span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="survey-actions"><a href="hobby_recommendation.php" class="btn-secondary">다시 설문하기</a></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 오른쪽: 새로운 통계 사이드바 -->
            <div class="right-section">
                 <div class="bg-white p-8 rounded-lg shadow-lg sticky top-32">
                    <h2 class="text-xl font-bold mb-4 border-b-2 border-blue-500 pb-2">MOIT 재미있는 통계</h2>
                    <div class="space-y-6 mt-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-100 text-blue-500 rounded-lg"></div>
                            <div>
                                <p class="font-bold text-gray-800 text-lg"><?php echo $stats['totalMeetings']; ?> 개</p>
                                <span class="text-sm text-gray-500">총 모임 수</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-100 text-blue-500 rounded-lg"></div>
                            <div>
                                <p class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($stats['popularCategory']); ?></p>
                                <span class="text-sm text-gray-500">가장 인기있는 카테고리</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-100 text-blue-500 rounded-lg"></div>
                            <div>
                                <p class="font-bold text-gray-800 text-lg"><?php echo $stats['newUsersThisWeek']; ?> 명</p>
                                <span class="text-sm text-gray-500">이번 주 새 멤버</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/js/navbar.js"></script>
    <!-- 기존 JavaScript 로직 유지 -->
    <script>
        const surveyForm = document.getElementById('surveyForm');
        if (surveyForm) {
            let currentStep = 1;
            const totalSteps = 15;
            const questionSteps = document.querySelectorAll('.question-step');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const part1Header = document.getElementById('part1-header');
            const part2Header = document.getElementById('part2-header');

            const allRadioButtons = surveyForm.querySelectorAll('input[type="radio"]');
            allRadioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (currentStep < totalSteps) {
                        setTimeout(() => { if (nextBtn.style.display !== 'none') { nextBtn.click(); } }, 350);
                    }
                });
            });

            updateStepDisplay();
            updateProgress();

            prevBtn.addEventListener('click', function() {
                if (currentStep > 1) { currentStep--; updateStepDisplay(); updateProgress(); }
            });

            nextBtn.addEventListener('click', function() {
                if (validateCurrentStep()) {
                    if (currentStep < totalSteps) { currentStep++; updateStepDisplay(); updateProgress(); }
                } else { alert('답변을 선택해주세요.'); }
            });

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (validateCurrentStep()) {
                    submitBtn.textContent = '분석 중...';
                    submitBtn.disabled = true;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'submit_survey';
                    hiddenInput.value = 'true';
                    surveyForm.appendChild(hiddenInput);
                    surveyForm.submit();
                } else { alert('마지막 질문에 답변해주세요.'); }
            });

            function updateStepDisplay() {
                questionSteps.forEach(step => step.classList.remove('active'));
                const currentQuestionStep = document.querySelector(`.question-step[data-step="${currentStep}"]`);
                if (currentQuestionStep) currentQuestionStep.classList.add('active');

                if (currentStep >= 1 && currentStep <= 5) {
                    part1Header.style.display = 'block';
                    part2Header.style.display = 'none';
                } else if (currentStep >= 6) {
                    part1Header.style.display = 'none';
                    part2Header.style.display = 'block';
                }

                prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
                nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-block';
                submitBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
            }

            function updateProgress() {
                const progress = (currentStep / totalSteps) * 100;
                if (progressFill) progressFill.style.width = progress + '%';
                if (progressText) progressText.textContent = `${currentStep} / ${totalSteps}`;
            }

            function validateCurrentStep() {
                const currentQuestionStep = document.querySelector(`.question-step[data-step="${currentStep}"]`);
                if (!currentQuestionStep) return false;
                const radioInput = currentQuestionStep.querySelector('input[type="radio"]');
                if (!radioInput) return false;
                const radioName = radioInput.name;
                const checkedRadio = currentQuestionStep.querySelector(`input[name="${radioName}"]:checked`);
                return checkedRadio !== null;
            }
        }
    </script>
</body>
</html>
