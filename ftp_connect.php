<?php
set_time_limit(300);



$ftp_server = 'service.russvet.ru';
$ftp_user_name = "pricat";
$ftp_user_pass = "vFtg23x";



$conn_id =ftp_connect($ftp_server,21021,90) or die ('connect error');
// проверка имени пользователя и пароля
$login_result = ftp_login($conn_id,$ftp_user_name,$ftp_user_pass);
ftp_pasv($conn_id, true);
if ((!$conn_id) || (!$login_result)) {
	echo "error FTP connect";
	echo "Try to FTP connect $ftp_server name $ftp_user_name!";
	exit;
} else {
	echo "connection FTP  $ftp_server name $ftp_user_name";
}
// получить содержимое текущей директории
$contents = ftp_nlist($conn_id, "/siberia");

$local_file = 'dev.tochka-elektriki.ru/public_html/PRICAT_DOWNLOAD.xml';
$server_file = array_pop($contents);
if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
	echo "write $local_file\n";
} else {
	echo "error write\n";
}
// закрытие соединения
ftp_close($conn_id);




