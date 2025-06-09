<?php
$saveDir = __DIR__ . '/json_answers';
if (!is_dir($saveDir)) {
    mkdir($saveDir, 0755, true);
}

$error = '';
$success = false;
$pythonOutput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId'] ?? '');
    $q1 = trim($_POST['q1'] ?? '');
    $q2 = trim($_POST['q2'] ?? '');
    $q3 = trim($_POST['q3'] ?? '');
    $q4 = trim($_POST['q4'] ?? '');
    $q5 = trim($_POST['q5'] ?? '');

    $q1_time = intval($_POST['q1_time'] ?? 0);
    $q2_time = intval($_POST['q2_time'] ?? 0);
    $q3_time = intval($_POST['q3_time'] ?? 0);
    $q4_time = intval($_POST['q4_time'] ?? 0);
    $q5_time = intval($_POST['q5_time'] ?? 0);

    if ($studentId === '' || !ctype_digit($studentId)) {
        $error = 'Please enter a valid Student ID (digits only).';
    } elseif ($q1 === '' || $q2 === '' || $q3 === '' || $q4 === '' || $q5 === '') {
        $error = 'Please answer all questions.';
    } else {
        $answers = [
            [
                "qnumber" => 1,
                "description" => $q1,
                "time_taken" => $q1_time
            ],
            [
                "qnumber" => 2,
                "description" => $q2,
                "time_taken" => $q2_time
            ],
            [
                "qnumber" => 3,
                "description" => $q3,
                "time_taken" => $q3_time
            ],
            [
                "qnumber" => 4,
                "description" => $q4,
                "time_taken" => $q4_time
            ],
            [
                "qnumber" => 5,
                "description" => $q5,
                "time_taken" => $q5_time
            ]
        ];
        $filename = "answers.json";
        $filePath = $saveDir . '/' . $filename;
        $allData = [];
        if (file_exists($filePath)) {
            $allData = json_decode(file_get_contents($filePath), true) ?? [];
        }
        $allData[$studentId] = $answers;
        $jsonString = json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($filePath, $jsonString) !== false) {
            $pythonScript = __DIR__ . '/algorithms.py';
            $outputFile = $saveDir . '/analysis.json';
            exec("python " . escapeshellarg($pythonScript) . " " . escapeshellarg($filePath) . " " . escapeshellarg($outputFile) . " 2>&1", $output, $return_var);
            $pythonOutput = implode("\n", $output);
            if ($return_var === 0) {
                $success = true;
            } else {
                $error = 'Error processing answers: ' . $pythonOutput;
            }
        } else {
            $error = 'Error saving JSON file.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login Form</title>
    <style>
        
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--bg);
            color: var(--text);
            transition: background-color 0.3s, color 0.3s;
        }

        :root {
            --bg: #ffffff;
            --text: #000000;
            --accent: #4a90e2;
        }

        .dark-mode {
            --bg: #121212;
            --text: #e0e0e0;
            --accent: #90caf9;
        }

        .question {
            background: rgba(0, 0, 0, 0.03);
            border-left: 4px solid var(--accent);
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .question label {
            display: block;
            margin-top: 10px;
            cursor: pointer;
        }

        button, input[type="submit"] {
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #357ab8;
        }

        #progress-container {
            margin-bottom: 30px;
        }

        #progress {
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        @media (max-width: 600px) {
            body {
            padding: 10px;
            }

            .question {
            padding: 10px;
            }
        }


        body {
            font-family: Times New Roman, Times, serif;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .main-box {
            border: 1px solid #888;
            padding: 20px 30px;
            margin: 30px auto;
            width: 700px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #888;
            text-align: center;
        }
        .student-id-label {
            display: block;
            margin-top: 15px;
            font-size: 16px;
        }
        .student-id-input {
            width: 200px;
            padding: 8px 10px;
            font-size: 15px;
            margin-top: 5px;
            border: 1px solid #888;
        }
        .question-label {
            margin-top: 25px;
            font-weight: bold;
            font-size: 16px;
            display: block;
        }
        .question-input {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 15px;
            font-size: 15px;
            padding: 8px 10px;
            border: 1px solid #888;
        }
        .question-textarea {
            width: 100%;
            height: 100px;
            margin-top: 8px;
            margin-bottom: 15px;
            font-size: 15px;
            padding: 8px 10px;
            border: 1px solid #888;
            resize: vertical;
            font-family: Times New Roman, Times, serif;
        }
        .divider {
            border-top: 1px solid #888;
            margin: 20px 0;
        }
        .submit-btn {
            margin-top: 20px;
            font-size: 16px;
            padding: 8px 20px;
            background-color: #f0f0f0;
            border: 1px solid #888;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #e0e0e0;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }
        .error {
            color: #b00;
            background-color: #ffebee;
            border: 1px solid #ef9a9a;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
        }
        
        body {
            background-color: #fff;
            color: #000;
            transition: background-color 0.3s, color 0.3s;
        }

            .dark-mode {
        background-color: #121212;
        color: #e0e0e0;
        }

        .dark-mode input,
        .dark-mode textarea,
        .dark-mode select {
        background-color: #1e1e1e;
        color: #e0e0e0;
        border: 1px solid #444;
        }

        .dark-mode button {
        background-color: #1f6feb;
        color: white;
        }


        .dark-mode textarea,
        .dark-mode input,
        .dark-mode select {
            border-color: #555;
        }

        button {
            cursor: pointer;
        }



    </style>
</head>
<body>
    <!-- دکمه تغییر تم -->
<button id="themeToggle" style="float: left; margin: 10px;">Dark/Light Theme</button>
<div id="progress-container" style="margin: 20px 0;">
  <label for="progress">Quiz Progress:</label>
  <progress id="progress" value="0" max="100" style="width: 100%; height: 20px;"></progress>
  <p id="progress-text">0% completed</p>
</div>


<div class="main-box">
    <div class="login-title">Login Form</div>
    
    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">Your answers were saved successfully.</div>
    <?php endif; ?>
    
    <form method="post" action="" id="studentForm" autocomplete="off">
        <label class="student-id-label">Student ID</label>
        <input type="text" id="studentId" name="studentId" required pattern="\d+" class="student-id-input" value="<?= htmlspecialchars($_POST['studentId'] ?? '') ?>" />
        
        <div class="divider question"></div>
        
        <label class="question-label"><b>Question 1:</b> Where is the capital city of France?</label>
        <input type="text" id="q1" name="q1" class="question-input" required value="<?= htmlspecialchars($_POST['q1'] ?? '') ?>" />
        <input type="hidden" name="q1_time" id="q1_time" value="0" />
        
        <div class="divider question"></div>
        
        <label class="question-label"><b>Question 2:</b> Solve for x in the equation: 2x + 5 = 15. Write the solution.</label>
        <input type="text" id="q2" name="q2" class="question-input" required value="<?= htmlspecialchars($_POST['q2'] ?? '') ?>" />
        <input type="hidden" name="q2_time" id="q2_time" value="0" />
        
        <div class="divider"></div>
        
        <label class="question-label"><b>Question 3:</b> If all roses are flowers and some flowers fade quickly, which of the following statements must be true? Explain it.</label>
        <textarea id="q3" name="q3" class="question-textarea" required><?= htmlspecialchars($_POST['q3'] ?? '') ?></textarea>
        <input type="hidden" name="q3_time" id="q3_time" value="0" />
        
        <div class="divider question"></div>
        
        <label class="question-label"><b>Question 4:</b> What is the chemical formula for water? How was the first soup made?</label>
        <textarea id="q4" name="q4" class="question-textarea" required><?= htmlspecialchars($_POST['q4'] ?? '') ?></textarea>
        <input type="hidden" name="q4_time" id="q4_time" value="0" />
        
        <div class="divider question"></div>
        
        <label class="question-label"><b>Question 5:</b> Write one of Ferdowsi's books, a little bio about him, and include your favorite poem from him.</label>
        <textarea id="q5" name="q5" class="question-textarea" required><?= htmlspecialchars($_POST['q5'] ?? '') ?></textarea>
        <input type="hidden" name="q5_time" id="q5_time" value="0" />
        
        <button type="submit" class="submit-btn">Submit</button>
    </form>
</div>

<script>
let lastTime = Date.now();
const totalQuestions = 5;

function setQuestionTime(questionNumber) {
    const now = Date.now();
    if (questionNumber > 1) {
        document.getElementById(`q${questionNumber-1}_time`).value = Math.round((now - lastTime) / 1000);
    }
    lastTime = now;
}

for(let i = 1; i <= totalQuestions; i++) {
    document.getElementById('q'+i).addEventListener('focus', function() {
        setQuestionTime(i);
    });
}

document.getElementById('q1').addEventListener('focus', function() {
    lastTime = Date.now();
});

document.getElementById('studentForm').addEventListener('submit', function() {
    const now = Date.now();
    document.getElementById(`q${totalQuestions}_time`).value = Math.round((now - lastTime) / 1000);
});
const toggleBtn = document.getElementById("themeToggle");
  const currentTheme = localStorage.getItem("theme");

  // اگر قبلاً تم تاریک ذخیره شده، اعمال کن
  if (currentTheme === "dark") {
    document.body.classList.add("dark-mode");
  }

  toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    // ذخیره تم انتخاب‌شده
    if (document.body.classList.contains("dark-mode")) {
      localStorage.setItem("theme", "dark");
    } else {
      localStorage.setItem("theme", "light");
    }
  });
  
  const inputs = document.querySelectorAll('input[type="radio"], input[type="checkbox"], textarea, select');
  const progressBar = document.getElementById('progress');
  const progressText = document.getElementById('progress-text');

  function updateProgress() {
    let answered = 0;
    const totalQuestions = document.querySelectorAll('.question').length;

    document.querySelectorAll('.question').forEach((question) => {
      const hasAnswer = question.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, textarea:not(:placeholder-shown), select:valid').length > 0;
      if (hasAnswer) answered++;
    });

    const percent = Math.round((answered / totalQuestions) * 100);
    progressBar.value = percent;
    progressText.textContent = `${percent}% completed`;
  }

  inputs.forEach(input => {
    input.addEventListener('input', updateProgress);
    input.addEventListener('change', updateProgress);
  });

  // Initial load
  updateProgress();


</script>
</body>
</html>