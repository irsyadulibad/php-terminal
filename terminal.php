<?php
$req = json_decode(file_get_contents('php://input', true));
$userTerm = shell_exec("whoami");

function json_output($output) {
    header('Content-Type: application/json');
    echo json_encode($output);
}
$os = PHP_OS_FAMILY;
$branch = shell_exec('git branch');

if(PHP_OS_FAMILY == "Windows"){
    $locationInfo = shell_exec("cd");
    $path = "set PATH=C:\Windows\system32;C:\Windows;C:\Windows\System32\Wbem;C:\Windows\System32\WindowsPowerShell\v1.0\;C:\Windows\System32\OpenSSH\;C:\Program Files\Git\cmd;C:\composer;C:\laragon\bin\php\php-8-latest;";
}else{
    $path = "";
    $locationInfo = shell_exec("pwd");
}

if(isset($req->command)) {
    $res = shell_exec($path."&& {$req->command} 2>&1");
    return json_output(['output' => $res]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal - <?=$os;?></title>
</head>
<body>
    <div id="output"></div>
    <div class="wrap-command">
     <div class="col">
         <label><span class="user"><?=$userTerm?></span><span class="location"><?=$locationInfo?></span><?=$branch?></label>
     </div>
     <div class="col flex">  
      <label for="">$</label>
         <input type="text" name="command" id="command" autocomplete="off" autofocus>
     </div>
    </div>
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
                } else if(this.value == 'exit'){
                   const exit = confirm("Do yo want to leave?");
                   if(exit) window.close();
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

        window.addEventListener("beforeunload", function(e) {
            e.preventDefault();
            e.returnValue = "Do you want to end this session?";
        }); 
    </script>
    <style type="text/css">
    body{
        padding: 10px;
        background: #000;
        font-family: 'Segoe UI', sans-serif;
    }
    h2{
        text-align: center;
        color: #fff;
    }
    label{
        color: #fff;
    }
    form{
        width: 100%;
        box-sizing: border-box;
    }
    input{
        outline: none;
        padding: 2px 5px;
        border: none;
        background: #000;
        width: 100%;
        box-sizing: border-box;
        color: #fff;
         font-family: 'Segoe UI', sans-serif;
        box-sizing: border-box;
    }
    .location{
        color: yellowgreen;
    }
    #output{
        color: #fff;
        font-family: 'consolas', sans-serif;
        font-size: 13px;
    }
    #port{
        color: #fff;
        float: right;
    }
    .wrap-command{
        display: block;
        width: 100%;
    } 
    .wrap-command p{
        max-width: 400px;
    }    
    .wrap-command label{
        color:#fff;
    }

    .col{
        width: 100%;
    }
    .flex{
        display: flex;
    }
    .flex label{
        color: gray;
    }
    .user{
        color: green;
    } 
</style>
</body>
</html>
