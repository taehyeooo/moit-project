<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

if (isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = trim($_POST['id']);
    $password = $_POST['password'];
    
    if (empty($id) || empty($password)) {
        $error_message = '아이디와 비밀번호를 모두 입력해주세요.';
    } else {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT id, name, nickname, password_hash FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_nickname'] = $user['nickname'];

                redirect('../index.php');
            } else {
                $error_message = '아이디 또는 비밀번호가 올바르지 않습니다.';
            }
        } catch (PDOException $e) {
            $error_message = '로그인 중 오류가 발생했습니다: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - MOIT</title>
    <link rel="stylesheet" href="../assets/index-DKv234qG.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center items-center p-4">
        <div class="max-w-md w-full mx-auto">
            <div class="text-center mb-8">
                <a href="../index.php" class="text-3xl font-bold text-blue-600">MOIT</a>
                <h2 class="mt-2 text-2xl font-bold text-gray-900">로그인</h2>
                <p class="mt-2 text-sm text-gray-600">다시 돌아오신 것을 환영합니다</p>
            </div>

            <div class="bg-white py-8 px-4 shadow-md rounded-lg sm:px-10">
                <form class="space-y-6" method="POST">
                    <?php if ($error_message): ?>
                        <p class="text-sm text-red-600 text-center"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>

                    <div>
                        <label for="id" class="text-sm font-medium text-gray-700">아이디</label>
                        <input id="id" name="id" type="text" required value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>"
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                    </div>
                    <div>
                        <label for="password" class="text-sm font-medium text-gray-700">비밀번호</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"/>
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">로그인 상태 유지</label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">비밀번호 찾기</a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            로그인
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">또는</span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div>
                            <button disabled class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 cursor-not-allowed">
                                Google로 로그인
                            </button>
                        </div>
                        <div>
                            <button disabled class="w-full inline-flex justify-center py-2 px-4 border border-yellow-400 rounded-md shadow-sm bg-yellow-400 text-sm font-medium text-black hover:bg-yellow-500 cursor-not-allowed">
                                카카오로 로그인
                            </button>
                        </div>
                    </div>
                </div>
            </div>
             <p class="mt-4 text-center text-sm text-gray-600">
                아직 계정이 없으신가요? <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">회원가입</a>
            </p>
            <p class="mt-2 text-center text-sm text-gray-600">
                <a href="../index.php" class="font-medium text-gray-500 hover:text-gray-700">홈으로 돌아가기</a>
            </p>
        </div>
    </div>
</body>
</html>
