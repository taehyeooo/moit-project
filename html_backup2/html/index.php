<?php
// MOIT 홈페이지
require_once 'php/config.php';

$site_title = "MOIT";
$main_title = "취미를 찾고, 사람을 만나고, 함께 즐기세요";
$sub_title = "AI 기반 취미 추천 서비스와 모임을 만들고 함께 즐겨보세요.";

// 로그아웃 메시지 처리
$logout_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $logout_message = '성공적으로 로그아웃되었습니다.';
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link rel="stylesheet" href="assets/index-DKv234qG.css">
    <link rel="stylesheet" href="css/navbar-style.css">
</head>
<body>
    <?php require_once 'php/navbar.php'; ?>

    <!-- 메인 컨테이너 -->
    <main class="bg-black text-white min-h-screen flex items-center">
        <div class="container mx-auto px-4">
            <?php if ($logout_message): ?>
                <div class="logout-message" style="position: absolute; top: 80px; left: 50%; transform: translateX(-50%); background-color: #4CAF50; color: white; padding: 15px; border-radius: 5px; z-index: 100;">
                    <?php echo htmlspecialchars($logout_message); ?>
                </div>
            <?php endif; ?>
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <!-- 왼쪽 텍스트 영역 -->
                <div class="text-center md:text-left">
                    <h1 class="text-4xl lg:text-5xl font-bold leading-tight mb-4">
                        <?php echo $main_title; ?> - <span class="text-blue-500">MOIT</span>
                    </h1>
                    <p class="text-lg text-gray-300 mb-8">
                        <?php echo $sub_title; ?>
                    </p>
                    <a href="php/introduction.php" class="px-6 py-3 bg-transparent border border-white rounded-full hover:bg-blue-500 hover:border-blue-500 transition-colors duration-300 flex items-center gap-2 mx-auto md:mx-0" style="width: fit-content;">
                        자세히 보기
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>

                <!-- 오른쪽 이미지 영역 -->
                <div>
                    <img src="assets/home1Run-NyG7OoQ5.jpg" alt="Hobby activity" class="rounded-lg w-full h-auto object-cover" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-300">
      <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 class="text-xl font-bold mb-4">MOIT 소개</h3>
            <p class="text-gray-400">
              MOIT는 취미를 통해 사람들을 연결하는 모임 플랫폼입니다. 새로운 관심사를 발견하고, 좋은 사람들과 함께 즐거운 시간을 보내세요.
            </p>
          </div>
          
          <div>
            <h3 class="text-xl font-bold mb-4">빠른 링크</h3>
            <ul class="space-y-2">
              <li><a href="/" class="hover:text-white transition-colors">홈</a></li>
              <li><a href="php/introduction.php" class="hover:text-white transition-colors">소개</a></li>
              <li><a href="php/hobby_recommendation.php" class="hover:text-white transition-colors">취미 추천</a></li>
              <li><a href="php/meeting.php" class="hover:text-white transition-colors">모임</a></li>
              <li><a href="#" class="hover:text-white transition-colors">문의하기</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-xl font-bold mb-4">고객 지원</h3>
            <ul class="space-y-2 text-gray-400">
              <li>문의/제휴</li>
              <li>이메일: contact@moit.com</li>
              <li>운영 시간: 평일 10:00 - 18:00</li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-xl font-bold mb-4">소셜 미디어</h3>
            <p class="text-gray-400">준비 중입니다.</p>
          </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
          <p>&copy; 2024 MOIT. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <script src="/js/navbar.js"></script>
</body>
</html>
