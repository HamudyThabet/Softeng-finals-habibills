<?php
require_once 'connection.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($fullName) && !empty($email) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Prepared statement query architecture to prevent SQL Injections
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);

        if ($stmt->execute()) {
            // EXACT REQUESTED LINK MECHANIC
            $message = "Register complete. <a href='index.html' class='underline font-bold hover:text-stripe-slate ml-1'>[Back to Login?]</a>";
            $messageClass = "text-stripe-success bg-stripe-success/10 border-stripe-success/20 text-center text-sm py-3";
        } else {
            if ($conn->errno == 1062) {
                $message = "This email node token is already linked to an existing account.";
            } else {
                $message = "Database synchronization fault: " . $conn->error;
            }
            $messageClass = "text-red-500 bg-red-50 border-red-200";
        }
        $stmt->close();
    } else {
        $message = "All validation parameters must be populated.";
        $messageClass = "text-red-500 bg-red-50 border-red-200";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabiBills // Initialize Identity Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        stripe: {
                            slate: '#0a2540',
                            text: '#425466',
                            textDark: '#1a1f36',
                            accent: '#635bff',
                            border: '#e3e8ee',
                            bgLight: '#f7fafc',
                            success: '#00d4b6'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .stripe-gradient-bg {
            background: radial-gradient(circle at 70% 10%, rgba(99, 91, 255, 0.15) 0%, transparent 45%),
                        radial-gradient(circle at 10% 80%, rgba(0, 212, 182, 0.12) 0%, transparent 40%),
                        #ffffff;
        }
    </style>
</head>
<body class="stripe-gradient-bg text-stripe-text min-h-screen flex flex-col justify-center items-center px-6 antialiased selection:bg-stripe-accent selection:text-white relative">

    <div class="absolute inset-0 pointer-events-none opacity-40 overflow-hidden">
        <div class="absolute top-[-10%] right-[-10%] w-[60vw] h-[60vw] rounded-full bg-[#635bff] filter blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-[#00d4b6] filter blur-[100px]"></div>
    </div>

    <div class="w-full max-w-md bg-white border border-stripe-border p-8 rounded-2xl shadow-xl shadow-stripe-slate/5 space-y-6 relative z-10 my-12">
        <div class="space-y-2">
            <span class="text-xl font-bold text-stripe-slate tracking-tight flex items-center gap-2 mb-4">
                <span class="w-4 h-4 bg-stripe-accent rounded-md transform rotate-12"></span>
                HabiBills
            </span>
            <h2 class="text-2xl font-bold text-stripe-slate tracking-tight">Create your operator profile.</h2>
            <p class="text-sm text-stripe-text">Deploy beautiful financial clarity on your terms.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-xl border text-xs font-semibold tracking-wide <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-stripe-slate uppercase tracking-wider">Full Name</label>
                <input type="text" name="full_name" required class="w-full bg-stripe-bgLight border border-stripe-border rounded-lg px-4 py-3 text-stripe-textDark text-sm focus:outline-none focus:border-stripe-accent">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-stripe-slate uppercase tracking-wider">Email</label>
                <input type="email" name="email" required class="w-full bg-stripe-bgLight border border-stripe-border rounded-lg px-4 py-3 text-stripe-textDark text-sm focus:outline-none focus:border-stripe-accent">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-stripe-slate uppercase tracking-wider">Password</label>
                <input type="password" name="password" required class="w-full bg-stripe-bgLight border border-stripe-border rounded-lg px-4 py-3 text-stripe-textDark text-sm focus:outline-none focus:border-stripe-accent">
            </div>
            
            <button type="submit" class="w-full bg-stripe-accent hover:bg-stripe-slate text-white font-semibold text-sm py-3 rounded-lg shadow-sm transition duration-300 transform hover:-translate-y-0.5 mt-2 cursor-pointer">
                Create Account
            </button>
        </form>

        <div class="border-t border-stripe-border pt-4 text-center text-xs text-stripe-text">
            Already running an active session? 
            <a href="index.html" class="text-stripe-accent font-semibold hover:underline ml-1">Access Terminal →</a>
        </div>
    </div>
</body>
</html>
