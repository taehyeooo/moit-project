<?php
// MOIT 모임 페이지
require_once 'config.php';

// 로그인 확인
if (!isLoggedIn()) {
    redirect('login.php');
}

$site_title = "MOIT - 모임";
$current_user_id = $_SESSION['user_id'] ?? null;

// DB에서 실제 모임 목록을 가져옵니다.
try {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT 
            m.id, m.title, m.description, m.category, m.location, 
            m.max_members, m.image_path, m.created_at, m.organizer_id,
            m.meeting_date, m.meeting_time,
            u.nickname AS organizer_nickname,
            (SELECT COUNT(*) FROM meeting_participants mp WHERE mp.meeting_id = m.id) AS current_members_count,
            (CASE 
                WHEN EXISTS (
                    SELECT 1 FROM meeting_participants mp 
                    WHERE mp.meeting_id = m.id AND mp.user_id = :current_user_id
                ) THEN 1
                ELSE 0 
            END) AS is_joined
        FROM meetings m
        JOIN users u ON m.organizer_id = u.id
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['current_user_id' => $current_user_id]);
    $meetings = $stmt->fetchAll();

} catch (PDOException $e) {
    $meetings = [];
    error_log("Meeting list fetch error: " . $e->getMessage());
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
    <link rel="stylesheet" href="../css/meeting-style.css">
</head>
<body class="bg-gray-50">
    <?php require_once 'navbar.php'; ?>

    <div class="min-h-screen py-32">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-8 text-center">관심사별 정모 일정</h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-3">
                    <!-- 카테고리 필터 -->
                    <div class="max-w-full mx-auto p-4 bg-white rounded-lg shadow mb-8">
                        <div class="flex flex-wrap justify-center gap-2" id="category-filter-buttons">
                            <button class="category-btn active" data-category="">전체</button>
                            <button class="category-btn" data-category="취미 및 여가">취미 및 여가</button>
                            <button class="category-btn" data-category="운동 및 액티비티">운동 및 액티비티</button>
                            <button class="category-btn" data-category="성장 및 배움">성장 및 배움</button>
                            <button class="category-btn" data-category="문화 및 예술">문화 및 예술</button>
                            <button class="category-btn" data-category="푸드 및 드링크">푸드 및 드링크</button>
                            <button class="category-btn" data-category="여행 및 탐방">여행 및 탐방</button>
                            <button class="category-btn" data-category="봉사 및 참여">봉사 및 참여</button>
                        </div>
                    </div>

                    <!-- 모임 카드 목록 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="meeting-cards-container">
                        <?php if (empty($meetings)): ?>
                            <p class="col-span-full text-center text-gray-500 py-10">현재 생성된 모임이 없습니다.</p>
                        <?php else: ?>
                            <?php foreach ($meetings as $meeting): ?>
                                <?php
                                    $current_members = $meeting['current_members_count'] + 1;
                                ?>
                                <div class="meeting-card block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 group"
                                     data-id="<?php echo $meeting['id']; ?>"
                                     data-category="<?php echo htmlspecialchars($meeting['category']); ?>"
                                     data-location="<?php echo htmlspecialchars($meeting['location']); ?>"
                                     data-title="<?php echo htmlspecialchars($meeting['title']); ?>"
                                     data-is-joined="<?php echo $meeting['is_joined'] ? 'true' : 'false'; ?>"
                                     data-organizer-id="<?php echo $meeting['organizer_id']; ?>"
                                     data-is-full="<?php echo ($current_members >= $meeting['max_members']) ? 'true' : 'false'; ?>">
                                    <div class="overflow-hidden">
                                        <img src="../<?php echo htmlspecialchars($meeting['image_path'] ?? 'assets/default_image.png'); ?>" alt="<?php echo htmlspecialchars($meeting['title']); ?>" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300" />
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-lg font-bold mb-2 truncate"><?php echo htmlspecialchars($meeting['title']); ?></h3>
                                        <p class="text-sm text-gray-600 mb-1">📍 <?php echo htmlspecialchars($meeting['location']); ?></p>
                                        <p class="text-sm text-gray-600 mb-3">🗓️ <?php echo htmlspecialchars($meeting['meeting_date']); ?></p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center -space-x-2">
                                            </div>
                                            <span class="font-semibold"><?php echo $current_members; ?> / <?php echo $meeting['max_members']; ?> 명</span>
                                        </div>
                                    </div>
                                    <div class="hidden-details" style="display:none;">
                                        <span class="description"><?php echo htmlspecialchars($meeting['description']); ?></span>
                                        <span class="organizer-nickname"><?php echo htmlspecialchars($meeting['organizer_nickname']); ?></span>
                                        <span class="meeting-time"><?php echo htmlspecialchars(substr($meeting['meeting_time'], 0, 5)); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 오른쪽 사이드바 -->
                <div class="lg:col-span-1">
                    <button id="open-create-modal-btn" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 mb-8 shadow">
                        + 새 모임 만들기
                    </button>

                    <div class="bg-white p-6 rounded-lg shadow sticky top-32">
                        <h2 class="text-xl font-bold mb-4">모임 검색</h2>
                        <div class="relative">
                            <input type="text" id="search-input" placeholder="제목으로 검색"
                                class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '_meeting_modals.php'; ?>

    <script src="/js/navbar.js"></script>
    <script>
        const currentUserId = '<?php echo $current_user_id; ?>';

        const createModal = document.getElementById('create-modal');
        const detailsModal = document.getElementById('details-modal');
        const openCreateModalBtn = document.getElementById('open-create-modal-btn');
        const meetingCardsContainer = document.getElementById('meeting-cards-container');
        
        const openModal = (modal) => modal.style.display = 'flex';
        const closeModal = (modal) => modal.style.display = 'none';

        openCreateModalBtn.addEventListener('click', () => openModal(createModal));

        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('modal-close-btn')) {
                    closeModal(modal);
                }
            });
        });

        meetingCardsContainer.addEventListener('click', (e) => {
            const card = e.target.closest('.meeting-card');
            if (!card) return;

            const id = card.dataset.id;
            const title = card.dataset.title;
            const description = card.querySelector('.description').textContent;
            const category = card.dataset.category;
            const location = card.dataset.location;
            const members = card.querySelector('.font-semibold').textContent.trim();
            const organizer = card.querySelector('.organizer-nickname').textContent;
            const imgSrc = card.querySelector('img').src;
            const meetingDate = card.querySelector('.text-sm.text-gray-600.mb-3').textContent.replace('🗓️','').trim();
            const meetingTime = card.querySelector('.meeting-time').textContent;

            const isJoined = card.dataset.isJoined === 'true';
            const organizerId = card.dataset.organizerId;
            const isFull = card.dataset.isFull === 'true';

            document.getElementById('modal-details-title').textContent = title;
            document.getElementById('modal-details-description').textContent = description;
            document.getElementById('modal-details-category').textContent = category;
            document.getElementById('modal-details-datetime').textContent = `${meetingDate} ${meetingTime}`;
            document.getElementById('modal-details-location').textContent = location;
            document.getElementById('modal-details-members').textContent = members;
            document.getElementById('modal-details-organizer').textContent = organizer;
            document.getElementById('modal-details-img').src = imgSrc;

            const modalFooter = document.getElementById('modal-details-footer');
            modalFooter.innerHTML = '';

            if (currentUserId !== organizerId) {
                if (isJoined) {
                    modalFooter.innerHTML = `
                        <form action="cancel_application.php" method="POST" onsubmit="return confirm('정말로 신청을 취소하시겠습니까?');">
                            <input type="hidden" name="meeting_id" value="${id}">
                            <button type="submit" class="btn-cancel">신청 취소</button>
                        </form>
                    `;
                } else {
                    const joinButton = document.createElement('button');
                    joinButton.type = 'submit';
                    joinButton.className = 'btn-primary';
                    joinButton.textContent = isFull ? '모집완료' : '신청하기';
                    if (isFull) joinButton.disabled = true;

                    const form = document.createElement('form');
                    form.action = 'join_meeting.php';
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="meeting_id" value="${id}">`;
                    form.appendChild(joinButton);
                    modalFooter.appendChild(form);
                }
            }

            openModal(detailsModal);

            const participantsList = document.getElementById('modal-details-participants-list');
            participantsList.innerHTML = '<li>목록을 불러오는 중...</li>';
            fetch(`get_participants.php?meeting_id=${id}`)
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
                });
        });

        const searchInput = document.getElementById('search-input');
        const categoryButtons = document.querySelectorAll('.category-btn');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeCategory = document.querySelector('.category-btn.active').dataset.category;

            document.querySelectorAll('.meeting-card').forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const cardCategory = card.dataset.category;

                const searchMatch = title.includes(searchTerm);
                const categoryMatch = !activeCategory || cardCategory === activeCategory;

                if (searchMatch && categoryMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('keyup', applyFilters);
        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                applyFilters();
            });
        });

        const createMeetingForm = document.getElementById('create-meeting-form');
        createMeetingForm.addEventListener('submit', function(e) {
        });

    </script>
</body>
</html>