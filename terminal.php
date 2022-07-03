<?php
$req = json_decode(file_get_contents('php://input', true));
$userTerm = trim(shell_exec("whoami"));

function json_output($output) {
    header('Content-Type: application/json');
    echo json_encode($output);
}
$os = PHP_OS_FAMILY;
$branch =   shell_exec('git rev-parse --abbrev-ref HEAD');

if(PHP_OS_FAMILY == "Windows"){
    $locationInfo = shell_exec("cd");
    $path = "set HOMEPATH=\Users\Sahrullah && set HOME=C:\Users\Sahrullah && set PATH=C:\Windows\system32;C:\Windows;C:\Windows\System32\Wbem;C:\Windows\System32\WindowsPowerShell\\v1.0\;C:\Windows\System32\OpenSSH\;C:\Program Files\Cloudflare\Cloudflare WARP;C:\Program Files\\nodejs\;C:\Program Files\Git\cmd;C:\composer;C:\laragon\bin\php\php-8-latest;C:\Program Files\Cloudflare\Cloudflare WARP\;C:\Program Files\Go\bin;C:\laragon\bin;C:\laragon\bin\apache\httpd-2.4.47-win64-VS16\bin;C:\laragon\bin\composer;C:\laragon\bin\git\bin;C:\laragon\bin\git\cmd;C:\laragon\bin\git\mingw64\bin;C:\laragon\bin\git\usr\bin;C:\laragon\bin\laragon\utils;C:\laragon\bin\mysql\mysql-5.7.33-winx64\bin;C:\laragon\bin\\nginx\\nginx-1.19.10;C:\laragon\bin\\ngrok;C:\laragon\bin\\nodejs\\node-v16;C:\laragon\bin\\notepad++;C:\laragon\bin\php\php-8-latest;C:\laragon\bin\\redis\\redis-x64-3.2.100;C:\laragon\bin\\telnet;C:\laragon\usr\bin;C:\Users\Sahrullah\AppData\Local\Yarn\config\global\\node_modules\.bin;C:\Users\Sahrullah\AppData\Roaming\Composer\\vendor\bin;C:\Users\Sahrullah\AppData\Roaming\\npm;C:\Users\Sahrullah\AppData\Local\Programs\Python\Python310\Scripts\;C:\Users\Sahrullah\AppData\Local\Programs\Python\Python310\;C:\Users\Sahrullah\AppData\Local\Microsoft\WindowsApps;C:\Users\Sahrullah\AppData\Local\Programs\Microsoft VS Code\bin;E:\scrcpy;D:\\exiftool;C:\Users\Sahrullah\go\bin";
}else{
    $path = "export HOME=/home/$userTerm";
    $locationInfo = shell_exec("pwd");
}

if(isset($req->command)) {
    $res = shell_exec($path."&&  {$req->command} 2>&1");
    return json_output(['output' => $res]);
    if(substr($req->command, 0, 5) == "nano"){
        $reqfile = substr(6, 20);
        system("vim  $reqfile `tty`");
    }
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
    <div id="output">

    </div>
    <div class="wrap-command">
     <div class="col">
         <label><span class="user"><?= $userTerm ?></span> <span class="location"><svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 19V9a2 2 0 0 0-2-2h-6.764a2 2 0 0 1-1.789-1.106l-.894-1.788A2 2 0 0 0 8.763 3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2Z"/></svg> <?= $locationInfo ?></span><svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M17.5 4C15.57 4 14 5.57 14 7.5c0 1.554 1.025 2.859 2.43 3.315c-.146.932-.547 1.7-1.23 2.323c-1.946 1.773-5.527 1.935-7.2 1.907V8.837c1.44-.434 2.5-1.757 2.5-3.337C10.5 3.57 8.93 2 7 2S3.5 3.57 3.5 5.5c0 1.58 1.06 2.903 2.5 3.337v6.326c-1.44.434-2.5 1.757-2.5 3.337C3.5 20.43 5.07 22 7 22s3.5-1.57 3.5-3.5c0-.551-.14-1.065-.367-1.529c2.06-.186 4.657-.757 6.409-2.35c1.097-.997 1.731-2.264 1.904-3.768C19.915 10.438 21 9.1 21 7.5C21 5.57 19.43 4 17.5 4zm-12 1.5C5.5 4.673 6.173 4 7 4s1.5.673 1.5 1.5S7.827 7 7 7s-1.5-.673-1.5-1.5zM7 20c-.827 0-1.5-.673-1.5-1.5a1.5 1.5 0 0 1 1.482-1.498l.13.01A1.495 1.495 0 0 1 7 20zM17.5 9c-.827 0-1.5-.673-1.5-1.5S16.673 6 17.5 6s1.5.673 1.5 1.5S18.327 9 17.5 9z"/></svg>[<?= $branch ?>]</label>
     </div>
     <div class="col flex">  
      <label for="">></label>
         <input type="text" name="command" id="command" autocomplete="off" autofocus>
     </div>
    </div>
    <script>
        var historyIndex = -1;
        const command = document.getElementById('command');
        const output = document.getElementById('output');
        const url = document.location.href;

        const hitCommand = () => {
            const lastCommand = command.value;
           
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
                           const prevCommand = document.createElement('p');
            prevCommand.classList.add("prevCommand");
            prevCommand.innerText = "> " + lastCommand;
                const p = document.createElement('p'); 
                p.innerText =  body.output;
                    output.appendChild(prevCommand);
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
        font-family: 'consolas', sans-serif;
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
    .prevCommand{
        color: gray;
    }
    .user{
        color: green;
    } 
</style>
</body>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
  feather.replace()
</script>

</html>
