<?php
if( $_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
	
	if( !empty($token)) {
		
		header("Content-type: application/json");
		
		$code = <<<EOFCODE
var r, offset = 0, batch, i = 0, ids=[];

while(i < 25) {
    i = i + 1;
    r = API.messages.getDialogs({count:200,offset:offset});
    batch = r.items@.message@.user_id;
    ids = ids + batch;
    offset = offset + 200;
    if( offset > r.count) i = 25;
}

return ids; 
EOFCODE;
		
		$ch = curl_init('https://api.vk.com/method/execute');
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => [
				"code" => $code,
				"access_token" => $token,
				"v" => 5.69,
			],
		]);
		
		$response = curl_exec($ch);
		$data = json_decode($response);
		
		if( !$data) {
			http_response_code(400);
			echo '{"error":"Error 1"}';
			return;
		}
		
		if( !isset( $data->response)) {
			http_response_code(400);
			if( isset($data->error)) {
				echo '{"error":'. $data->error->error_msg . '}';
			} else {
				echo '{"error":"Error 2"}';
			}
			return;
		}
		
		echo json_encode($data->response);
		return;
	}
}	 

?><!DOCTYPE html>

<html lang="ru">
<head>
	<meta charset="utf-8">

	<title>Диалоги</title>
	<meta name="description" content="Получить список id тех, с кем группа вела диалоги">
	<meta name="keywords" content="ВКонтакте,ВК,диалоги,id,список,парсинг">
	<meta name="robots" content="">
	
	<link rel="stylesheet" href="https://yastatic.net/bootstrap/3.3.6/css/bootstrap.min.css">
</head>

<body>
	<div class="container">
		<h1>Диалоги ВК</h1>
		<h4>Здесь можно скачать список ID тех, с кем группа вела диалоги.</h4>
		<p>Те, с кем группа вела диалоги, дали своё разрешение на получение от группы сообщений. Значит, можно иногда им рассылать какой-то текст. Этим инструментом можно получить список ID таких пользователей вашей группы. Чтобы разослать им сообщения, нужно воспользоваться каким-то другим сервисом, например, .... ?</p>
		
		<div class="row">
			<div class="col-md-8">
				<h4>1. Скопируйте сюда токен Сообщества:</h4>
				<div class="form-group">
<!--					 <label for="in-token">Токен сообщества</label> с правом доступа к Сообщениям Сообщества: -->
					<input type="text" class="form-control" id="in-token" placeholder="токен">
					<span class="help-block">Надо зайти в своей группе в меню Управление Сообществом – Работа с API. Там скопировать старый токен или создать новый. Убедитесь, что у токена установелены «Права доступа: сообщения сообщества»</span>
				</div>

<!--
				<div class="checkbox">
					<label>
						<input type="checkbox"> Check me out
					</label>
				</div>				  
-->

				<h4>2. Выберите формат файла:</h4>
				<div class="radio">
					<label>
						<input type="radio" name="optionsFormat" id="optionsRadios1" value="windows" checked>
						Формат под Windows, каждый id на новой строке.
					</label>
				</div>
				
				<div class="radio">
					<label>
						<input type="radio" name="optionsFormat" id="optionsRadios2" value="csv">
						Формат CSV для Excel.
					</label>
				</div>
				
				<h4>3. Получите файл:</h4>
				<button id="in-btn" class="btn btn-success">Скачать ID</button>
			</div>
			  
		</div>
	</div>
	
	<script src="https://yastatic.net/jquery/3.1.1/jquery.min.js"></script>
	<script src="download.js"></script>
	<script>
		
		$('#in-btn').on('click', function() {
			var format = $('input[name=optionsFormat]:checked').val();
			
			$.ajax({
				data:{token: $('#in-token').val()}
				,method: 'post'
				,success: function(r) {
					var data, filename;
					switch(format) {
						case 'csv':
							download(r.join("\n"), 'ids.csv', "text/csv");
							break;
							
						default:
						case 'windows':
							download(r.join("\r\n"), 'ids.txt', "text/plain");
					}
				}
			});
		});
		
	</script>

</body></html>
