<?php
// MOIT 마이페이지
require_once 'config.php';

// 로그인하지 않은 사용자는 로그인 페이지로 이동
if (!isLoggedIn()) {
    redirect('login.php');
}

$site_title = "MOIT - 마이페이지";

// DB에서 사용자의 모임 정보를 가져옵니다.
$created_meetings = [];
$joined_meetings = [];
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // 1. 내가 만든 모임 목록 조회
    $stmt_created = $pdo->prepare("
        SELECT 
            m.id, m.title, m.description, m.category, m.location, 
            m.max_members, m.image_path, m.created_at, m.organizer_id,
            (SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = m.id) + 1 AS current_members,
            '" . htmlspecialchars($_SESSION['user_nickname']) . "' AS organizer_nickname
        FROM meetings m
        WHERE m.organizer_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt_created->execute([$user_id]);
    $created_meetings = $stmt_created->fetchAll(PDO::FETCH_ASSOC);

    // 2. 내가 참여한 모임 목록 조회
    $stmt_joined = $pdo->prepare("
        SELECT 
            m.id, m.title, m.description, m.category, m.location, 
            m.max_members, m.image_path, m.created_at, m.organizer_id,
            u.nickname AS organizer_nickname,
            (SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = m.id) + 1 AS current_members
        FROM meetings m
        JOIN users u ON m.organizer_id = u.id
        JOIN meeting_participants mp ON m.id = mp.meeting_id
        WHERE mp.user_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt_joined->execute([$user_id]);
    $joined_meetings = $stmt_joined->fetchAll();

} catch (PDOException $e) {
    error_log("Mypage data fetch error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link rel="stylesheet" href="../assets/index-DKv234qG.css">
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/mypage-style.css"> 
</head>
<body class="bg-gray-100">
    <?php require_once 'navbar.php'; ?>

    <div class="min-h-screen py-32">
        <div class="container mx-auto px-4 max-w-5xl">
            <!-- 프로필 섹션 -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 flex items-center justify-between">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 bg-blue-500 rounded-full"></div> <!-- 임시 프로필 사진 -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_nickname']); ?> 님</h1>
                        <p class="text-gray-500 mt-1">오늘도 새로운 취미를 찾아보세요!</p>
                    </div>
                </div>
                <button class="bg-gray-200 text-gray-700 font-semibold px-5 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    프로필 수정
                </button>
            </div>
            
            <!-- 탭 메뉴 -->
            <div class="border-b border-gray-200 mb-6 tab-nav">
                <button class="tab-btn active px-4 py-3 font-semibold text-lg transition-colors duration-300 border-b-2 border-blue-500 text-blue-500" data-target="created-meetings">내가 만든 모임</button>
                <button class="tab-btn px-4 py-3 font-semibold text-lg transition-colors duration-300 text-gray-500 hover:text-gray-800" data-target="joined-meetings">내가 참여한 모임</button>
            </div>

            <!-- 모임 리스트 -->
            <div class="space-y-4 tab-content">
                <div id="created-meetings" class="tab-pane active">
                    <?php if (empty($created_meetings)): ?>
                        <p class="text-center text-gray-500 py-10">아직 직접 만든 모임이 없어요.</p>
                    <?php else: ?>
                        <?php foreach ($created_meetings as $meeting): ?>
                            <?php
                                $isRecruiting = $meeting['current_members'] < $meeting['max_members'];
                                $status_text = $isRecruiting ? '모집중' : '모집완료';
                                $status_color_class = $isRecruiting ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700';
                            ?>
                            <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-4">
                                <span class="bg-gray-100 text-gray-600 text-sm font-medium px-3 py-1 rounded-full whitespace-nowrap"><?php echo htmlspecialchars($meeting['category']); ?></span>
                                <p class="flex-grow font-bold text-gray-800 text-lg truncate"><?php echo htmlspecialchars($meeting['title']); ?></p>
                                <span class="text-gray-500 whitespace-nowrap"><?php echo $meeting['current_members']; ?> / <?php echo $meeting['max_members']; ?>명</span>
                                <span class="text-sm font-semibold px-3 py-1 rounded-full <?php echo $status_color_class; ?>"><?php echo $status_text; ?></span>
                                <button class="btn-details bg-blue-500 text-white font-semibold px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors whitespace-nowrap" data-meeting='<?php echo json_encode($meeting); ?>'>상세보기</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="joined-meetings" class="tab-pane">
                     <?php if (empty($joined_meetings)): ?>
                        <p class="text-center text-gray-500 py-10">아직 참여한 모임이 없어요.</p>
                    <?php else: ?>
                        <?php foreach ($joined_meetings as $meeting): ?>
                            <?php
                                $isRecruiting = $meeting['current_members'] < $meeting['max_members'];
                                $status_text = $isRecruiting ? '모집중' : '모집완료';
                                $status_color_class = $isRecruiting ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700';
                            ?>
                            <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-4">
                                <span class="bg-gray-100 text-gray-600 text-sm font-medium px-3 py-1 rounded-full whitespace-nowrap"><?php echo htmlspecialchars($meeting['category']); ?></span>
                                <p class="flex-grow font-bold text-gray-800 text-lg truncate"><?php echo htmlspecialchars($meeting['title']); ?> <span class="text-sm font-normal text-gray-500">(개설자: <?php echo htmlspecialchars($meeting['organizer_nickname']); ?>)</span></p>
                                <span class="text-gray-500 whitespace-nowrap"><?php echo $meeting['current_members']; ?> / <?php echo $meeting['max_members']; ?>명</span>
                                <span class="text-sm font-semibold px-3 py-1 rounded-full <?php echo $status_color_class; ?>"><?php echo $status_text; ?></span>
                                <button class="btn-details bg-blue-500 text-white font-semibold px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors whitespace-nowrap" data-meeting='<?php echo json_encode($meeting); ?>'>상세보기</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 기존 모달 HTML은 그대로 사용합니다 -->
    <div id="details-modal" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <button class="modal-close-btn">&times;</button>
            <img id="modal-details-img" src="" alt="모임 이미지" class="modal-img">
            <div class="modal-header">
                <h2 id="modal-details-title"></h2>
                <div>
                    <span id="modal-details-category" class="card-category"></span>
                    <span id="modal-details-status" class="card-status"></span>
                </div>
            </div>
            <div class="modal-body">
                <p id="modal-details-description"></p>
                <div class="modal-details-info">
                    <span>📍 장소: <strong id="modal-details-location"></strong></span>
                    <span>👥 인원: <strong id="modal-details-members"></strong></span>
                    <span>👤 개설자: <strong id="modal-details-organizer"></strong></span>
                </div>
                <div class="modal-details-participants">
                    <h4>참여자 목록</h4>
                    <ul id="modal-details-participants-list">
                    </ul>
                </div>
            </div>
            <div class="modal-footer" id="modal-details-footer">
            </div>
        </div>
    </div>

    <script src="/js/navbar.js"></script>
    <!-- 기존 JavaScript 로직은 그대로 사용합니다 -->
    <script>
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');

                tabBtns.forEach(b => {
                    b.classList.remove('active', 'border-blue-500', 'text-blue-500');
                    b.classList.add('text-gray-500', 'hover:text-gray-800');
                });
                btn.classList.add('active', 'border-b-2', 'border-blue-500', 'text-blue-500');
                btn.classList.remove('text-gray-500', 'hover:text-gray-800');

                tabPanes.forEach(pane => {
                    if (pane.id === targetId) {
                        pane.classList.add('active');
                    } else {
                        pane.classList.remove('active');
                    }
                });
            });
        });

        const detailsModal = document.getElementById('details-modal');
        const meetingLists = document.querySelectorAll('.space-y-4');

        const openModal = (modal) => modal.style.display = 'flex';
        const closeModal = (modal) => modal.style.display = 'none';

        detailsModal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('modal-close-btn')) {
                closeModal(detailsModal);
            }
        });

        meetingLists.forEach(list => {
            list.addEventListener('click', e => {
                if (!e.target.classList.contains('btn-details')) return;

                const meetingData = JSON.parse(e.target.dataset.meeting);
                
                const isRecruiting = meetingData.current_members < meetingData.max_members;
                const status_text = isRecruiting ? '모집중' : '모집완료';
                const status_class = isRecruiting ? 'recruiting' : 'completed';

                document.getElementById('modal-details-title').textContent = meetingData.title;
                document.getElementById('modal-details-description').textContent = meetingData.description;
                document.getElementById('modal-details-category').textContent = meetingData.category;
                document.getElementById('modal-details-status').textContent = status_text;
                document.getElementById('modal-details-status').className = 'card-status ' + status_class;
                document.getElementById('modal-details-location').textContent = meetingData.location;
                document.getElementById('modal-details-members').textContent = `${meetingData.current_members} / ${meetingData.max_members}`;
                document.getElementById('modal-details-organizer').textContent = meetingData.organizer_nickname;
                document.getElementById('modal-details-img').src = `../${meetingData.image_path || 'assets/default_image.png'}`;

                const modalFooter = document.getElementById('modal-details-footer');
                modalFooter.innerHTML = '';

                openModal(detailsModal);

                const participantsList = document.getElementById('modal-details-participants-list');
                participantsList.innerHTML = '<li>목록을 불러오는 중...</li>';

                fetch(`get_participants.php?meeting_id=${meetingData.id}`)
                    .then(response => response.json())
                    .then(data => {
                        participantsList.innerHTML = '';
                        if (data.error) {
                            participantsList.innerHTML = '<li>참여자 정보를 가져오는데 실패했습니다.</li>';
                        } else if (data.length > 0) {
                            data.forEach(participant => {
                                const li = document.createElement('li');
                                li.textContent = participant;
                                participantsList.appendChild(li);
                            });
                        } else {
                            participantsList.innerHTML = '<li>아직 참여자가 없습니다.</li>';
                        }
                    })
                    .catch(error => {
                        participantsList.innerHTML = '<li>참여자 정보를 가져오는데 실패했습니다.</li>';
                        console.error('Error fetching participants:', error);
                    });
            });
        });
    </script>
</body>
</html>
