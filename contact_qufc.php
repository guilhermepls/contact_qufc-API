<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$para = 'equipequfc@gmail.com';
$assunto = 'Fale Conosco - Equipe QUFC';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nome = $data['nome'];
    $email = $data['email'];
    $mensagem = $data['mensagem'];
    $anexos = $data['anexos'] ?? []; 

    $boundary = md5(uniqid(time()));

    $headers = array(
        'From: ' . $email,
        'Reply-To: ' . $email,
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary=' . $boundary
    );

    $corpo = "--" . $boundary . "\r\n";
    $corpo .= "Content-Type: text/plain; charset=utf-8\r\n";
    $corpo .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $corpo .= "Nome: " . $nome . "\n";
    $corpo .= "Email: " . $email . "\n\n";
    $corpo .= "Mensagem:\n" . $mensagem . "\r\n";

    foreach ($anexos as $index => $anexo) {
        $tipo = $anexo['tipo']; 
        $nome_arquivo = $anexo['nome'];
        $conteudo = base64_decode($anexo['conteudo']);

        $corpo .= "\r\n--" . $boundary . "\r\n";
        $corpo .= "Content-Type: " . $tipo . "; name=\"" . $nome_arquivo . "\"\r\n";
        $corpo .= "Content-Transfer-Encoding: base64\r\n";
        $corpo .= "Content-Disposition: attachment; filename=\"" . $nome_arquivo . "\"\r\n\r\n";
        $corpo .= chunk_split(base64_encode($conteudo)) . "\r\n";
    }

    $corpo .= "--" . $boundary . "--\r\n";

    try {
        if (mail($para, $assunto, $corpo, implode("\r\n", $headers))) {
            http_response_code(200);
            echo json_encode(['mensagem' => 'Email enviado com sucesso!']);
        } else {
            throw new Exception('Falha ao enviar email');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao enviar email: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}