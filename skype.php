<?php 
header('Content-Type: text/html; charset=utf-8');
include '/home/estoniac/public_html/crewnew.com/projects/pm/_include/head.php';
include '/home/estoniac/public_html/crewnew.com/projects/pm/_include/config.php'; 
?>
<h1>Get Skype chat history</h1>
<?php
$path = '/home/estoniac/public_html/crewnew.com/projects/skype/db/kasparpalgi.db';

download_skype_db($skype_url, $path);

$db = new SQLite3($path);
$results = $db->query('SELECT nsp_data FROM messagesv12');

echo '<table border="1"> <tr><th>ID</th><th>Chatname</th><th>Author</th><th>Date/Time</th><th>Body</th><th>Action</th></tr>';

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");	
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

while ($row = $results->fetchArray()) {
	$insert = 1;
	echo '<tr>';
	
	$messages = json_decode(utf8_decode($row[0]), true);
	
	$cuid = $messages['cuid'];
	$message = mysqli_real_escape_string($conn, $messages['content']);
	$datetime = date("Y-m-d H:i:s", $messages['createdTime'] / 1000);
	$chatname = str_replace("8:","", $messages['conversationId']);
	$author = str_replace("8:","", $messages['creator']);
	
	if (strpos($message, $author) !== false) $insert = 0; //If message content is just author's Skype username
	if (strpos($message, 'microsoft.card.popup') !== false) $insert = 0; //Skype adverts
	if (strpos($message, 'campaignId') !== false) $insert = 0; //Skype campaign
				
	if ($message != 'Hi' AND $message != 'Hii' AND $message != 'Hello ' AND $message != 'Hello' AND $message != 'Hello, how are you doing?' AND $message != 'Hi Kaspar' AND $message != 'Hey Kaspar' AND $message != '' AND $insert != 0)
	{ //Don't add messages containing only Hi or Hello AND ID is not empty AND message content isn't same as the author
		echo '<td>' . $cuid . '</td><td>' . $chatname . '</td><td>' . $author . '</td><td>' . $datetime . '<br>' . $messages['createdTime'] . '</td><td>' . $message . '</td>';
			
		$query = "SELECT cuid FROM crm_chats WHERE cuid='$cuid'"; //Check if already exists in DB
		$result = $conn->query($query);		
		if(mysqli_num_rows($result) > 0){
			echo "<td>Exists in DB</td>";
		}else{
			
			$contact_id = 0;
			$contact_type = 0;
			$from_contacts = "SELECT creId FROM cn_contacts WHERE skype='$chatname'"; //Check if skype ID is in DB under crew	
			$result = mysqli_query($conn, $from_contacts);
			$row = mysqli_fetch_assoc($result);
			if(mysqli_num_rows($result) > 0){
				$contact_id = $row['creId'];
				$contact_type = 2;
			}
			
			$from_clients = "SELECT vid FROM crm_clients WHERE skype='$chatname'"; //Check if skype ID is in DB under customers		
			$result = mysqli_query($conn, $from_clients);
			$row = mysqli_fetch_assoc($result);
			if(mysqli_num_rows($result) > 0){
				$contact_id = $row['vid'];
				$contact_type = 1;
			}
			
			$sql = "INSERT INTO crm_chats (cuid, contact_id, contact_type, user_id, contact, author, date_time, message) VALUES ('$cuid', '$contact_id', '$contact_type', '88', '$chatname', '$author', '$datetime', '$message')";
			if ($conn->query($sql) === TRUE) {
				echo "<td>Added to DB</td>";
			} 
			else {
				echo "<td>Error: " . $sql . "<br>" . $conn->error . '</td>';
			}			
		}
	}
	echo '</tr>';
}

function download_skype_db($skype_url, $path) {
	$fh = fopen($path, 'w');
	set_time_limit(0); // unlimited max execution time
	$options = array(
	  CURLOPT_FILE    => $fh,
	  CURLOPT_TIMEOUT =>  28800, // set this to 8 hours so we dont timeout on big files
	  CURLOPT_URL     => $skype_url,
	);


	$ch = curl_init();
	curl_setopt_array($ch, $options);
	curl_exec($ch);
	curl_close($ch);
	fclose($fh);
}
?>
</table>
</body>
</html>
