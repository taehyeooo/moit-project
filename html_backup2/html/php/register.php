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
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    
    if (!validateId($id)) {
        $error_message = '아이디는 4글자 이상의 영문, 숫자, 언더스코어만 사용 가능합니다.';
    } elseif (!validatePassword($password)) {
        $error_message = '비밀번호는 6글자 이상이어야 합니다.';
    } elseif ($password !== $confirm_password) {
        $error_message = '비밀번호가 일치하지 않습니다.';
    } elseif (!validateName($name)) {
        $error_message = '이름은 2~20글자 사이여야 합니다.';
    } elseif (!validateNickname($nickname)) {
        $error_message = '닉네임은 2~15글자 사이여야 합니다.';
    } elseif (!validateEmail($email)) {
        $error_message = '올바른 이메일 주소를 입력해주세요.';
    } else {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? OR nickname = ? OR email = ?");
            $stmt->execute([$id, $nickname, $email]);
            
            if ($stmt->fetch()) {
                $error_message = '이미 존재하는 아이디, 닉네임 또는 이메일입니다.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (id, password_hash, name, nickname, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id, $hashed_password, $name, $nickname, $email]);
                
                $success_message = '회원가입이 완료되었습니다. 로그인해주세요.';
            }
        } catch (PDOException $e) {
            $error_message = '회원가입 중 오류가 발생했습니다: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - MOIT</title>
    <link rel="stylesheet" href="../assets/index-DKv234qG.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-4">
        <div class="max-w-md w-full mx-auto">
            <div class="text-center mb-8">
                <a href="../index.php" class="text-3xl font-bold text-blue-600">MOIT</a>
                <h2 class="mt-2 text-2xl font-bold text-gray-900">회원가입</h2>
                <p class="mt-2 text-sm text-gray-600">새로운 취미와 사람들을 만나보세요</p>
            </div>

            <div class="bg-white py-8 px-4 shadow-md rounded-lg sm:px-10">
                <?php if ($success_message): ?>
                    <div class="text-center">
                        <p class="text-green-600"><?php echo htmlspecialchars($success_message); ?></p>
                        <a href="login.php" class="mt-4 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">로그인 페이지로 이동</a>
                    </div>
                <?php else: ?>
                    <form class="space-y-4" method="POST">
                        <?php if ($error_message): ?>
                            <p class="text-sm text-red-600 text-center"><?php echo htmlspecialchars($error_message); ?></p>
                        <?php endif; ?>

                        <div>
                            <label for="id" class="text-sm font-medium text-gray-700">아이디</label>
                            <input id="id" name="id" type="text" required value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>"
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                            <p class="mt-1 text-xs text-gray-500">4글자 이상의 영문, 숫자, 언더스코어(_)만 사용 가능</p>
                        </div>
                        <div>
                            <label for="password" class="text-sm font-medium text-gray-700">비밀번호</label>
                            <input id="password" name="password" type="password" required
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                            <p class="mt-1 text-xs text-gray-500">6글자 이상의 비밀번호</p>
                        </div>
                        <div>
                            <label for="confirm_password" class="text-sm font-medium text-gray-700">비밀번호 확인</label>
                            <input id="confirm_password" name="confirm_password" type="password" required
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div>
                            <label for="name" class="text-sm font-medium text-gray-700">이름</label>
                            <input id="name" name="name" type="text" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div>
                            <label for="nickname" class="text-sm font-medium text-gray-700">닉네임</label>
                            <input id="nickname" name="nickname" type="text" required value="<?php echo isset($_POST['nickname']) ? htmlspecialchars($_POST['nickname']) : ''; ?>"
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div>
                            <label for="email" class="text-sm font-medium text-gray-700">이메일</label>
                            <input id="email" name="email" type="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                회원가입
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <p class="mt-4 text-center text-sm text-gray-600">
                이미 계정이 있으신가요? <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">로그인</a>
            </p>
            <p class="mt-2 text-center text-sm text-gray-600">
                <a href="../index.php" class="font-medium text-gray-500 hover:text-gray-700">홈으로 돌아가기</a>
            </p>
        </div>
    </div>
</body>
</html>
