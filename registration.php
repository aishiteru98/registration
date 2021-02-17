require_once 'connection.php';

$response = array();

if(isset($_GET['apicall'])){
switch($_GET['apicall']){
case 'signup':
if(isTheseParametersAvailable(array('Nama','nik','Password','token'))){
$nama = $_POST['Nama'];
$nik = $_POST['nik'];
$password = md5($_POST['Password']);
$token = $_GET ['token'];

$stmt = $conn->prepare("SELECT id FROM pengguna WHERE nik = ?");
$stmt->bind_param("s", $nik);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
$response['error'] = true;
$response['message'] = 'User telah registrasi';
$stmt->close();
}
else {

$stmt = $conn->prepare("INSERT INTO pengguna(Nama, nik ,Password, token) VALUES (?,?,?,?)");
$stmt->bind_param("ssss", $nama, $nik, $password, $token);

if($stmt->execute()){
$stmt = $conn->prepare("SELECT id, id, nik, Nama, token FROM pengguna WHERE nik = ?");
$stmt->bind_param("s", $nik);
$stmt->execute();
$stmt->bind_result($userid, $id, $nama, $nik,$token);
$stmt->fetch();

$user = array(
'id'=>$id,
'Nama'=>$nama,
'nik'=>$nik,
'token' => $token,
);

$stmt->close();

$response['error'] = false;
$response['message'] = 'User berhasil register';
$response['user'] = $user;
}
}
}
else {
$response['error'] = true;
$response['message'] = 'required parameters are not available';
}

break;

case 'login':
if(isTheseParametersAvailable(array('nik', 'Password'))){
$nik = $_POST['nik'];
$password = md5($_POST['Password']);

$stmt = $conn->prepare("SELECT id, Nama, nik FROM pengguna WHERE nik = ? AND Password = ?");
$stmt->bind_param("ss", $nik, $password);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
$stmt->bind_result($id, $nama, $nik);
$stmt->fetch();

$user = array(
'id'=>$id,
'Nama'=>$nama,
'nik'=>$nik
);

$response['error'] = false;
$response['message'] = 'Login berhasil';
$response['user'] = $user;
}
else {
$response['error'] = false;
$response['message'] = 'Invalid nik or PASSWORD';
}
}

break;

default:

$response['error'] = true;
$response['message'] = 'Invalid Operation Called';
}
}
else {
$response['error'] = true;
$response['message'] = 'Invalid API Call';
}

echo json_encode($response);
function isTheseParametersAvailable($params){
foreach($params as $param){
if(!isset($_POST[$param])){
return false;
}
}
return true;
}
