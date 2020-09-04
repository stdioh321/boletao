<?php

require_once "vendor/autoload.php";
// header('Content-Type: application/pdf');
// header('Content-Disposition: inline; filename="YourFileName.pdf"');

// require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

use OpenBoleto\Banco\Itau;
use OpenBoleto\Agente;
use OpenBoleto\Banco\BancoDoBrasil;
use H2P\Converter\PhantomJS;
use H2P\TempFile;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$c = new \Slim\Container(); //Create Your container

//Override the default Not Found Handler before creating App
// $c['notFoundHandler'] = function ($c) {
//     return function ($request, $response) use ($c) {
//         // header("Refresh: 3;URL:" . $_SERVER['HTTP_HOST'] . "/nada");
//         // header("Location: /nada");
//         // echo "Not Found";
//         // die();
//         return $response->withStatus(404)
//             ->withHeader('Content-Type', 'text/html')
//             ->write('Page not foundddd');
//     };
// };
$app = new \Slim\App($c);
$app->get("/{banco}", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $banco = $args['banco'];
    $body = $request->getParsedBody();
    $banco = isset($banco) == true ? strtolower(strval($banco)) : 'Itau';
    $banco = ucfirst($banco);

    $sacado = new Agente($body['sac_nome'], $body['sac_doc'], $body['sac_endereco'], $body['sac_cep'], $body['sac_cidade'], isset($body['sac_uf']) == true ? strtoupper(strval($body['sac_uf'])) : null);
    $cedente = new Agente($body['ced_nome'], $body['ced_doc'], $body['ced_endereco'], $body['ced_cep'], $body['ced_cidade'], isset($body['ced_uf']) == true ? strtoupper(strval($body['ced_uf'])) : null);
    // $cedente = new Agente('Empresa de cosméticos LTDA', '02.123.123/0001-11', 'CLS 403 Lj 23', '71000-000', 'Brasília', 'DF');
    $dtVenc = isset($body['dtVenc']) == true ? DateTime::createFromFormat("Y-m-d", $body['dtVenc']) : new DateTime();
    $valor = $body['valor'] ? $body['valor'] : 0;
    $agencia = $body['agencia'];
    $conta = $body['conta'];
    if (class_exists("\\OpenBoleto\\Banco\\" . $banco)) $banco = "\\OpenBoleto\\Banco\\" . $banco;
    else {
        http_response_code(400);
        return "Bank not found";
    }
    $boleto = new $banco(array(
        // Parâmetros obrigatórios
        'dataVencimento' => $dtVenc,
        'valor' => $valor,
        // 'sequencial' => 1234567, // Para gerar o nosso número
        'sacado' => $sacado,
        'cedente' => $cedente,
        'agencia' => $agencia, // Até 4 dígitos
        // 'carteira' => 148,
        'conta' => $conta, // Até 8 dígitos
        // 'convenio' => 1234, // 4, 6 ou 7 dígitos
    ));
    $boleto->setImprimeInstrucoesImpressao(false);


    try {
        $html = $boleto->getOutput();
        $converter = new PhantomJS(array(
            'format' => PhantomJS::FORMAT_A3,
        ));

        // Convert destination accepts H2P\TempFile or string with the path to save the file
        $input = new TempFile($html, 'html');
        $path = __DIR__ . "/tmp";
        if (!is_dir($path)) mkdir($path);
        $file = "$path/page.pdf";
        $tmp = new TempFile();
        $converter->convert($input, $tmp);
        // header("Content-type:application/pdf");
        $rsp = $response->withHeader("Content-type", "application/pdf");
        $body = $rsp->getBody();
        $body->write($tmp->getContent());

        return $rsp->withBody($body);
    } catch (\Throwable $th) {
        //throw $th;
        echo $th->getMessage();
    }


    return $response;
});
$app->run();
return;
$json = (json_decode(file_get_contents("php://input")));
print_r($_REQUEST);
return;
if ($json != null) {
    print_r($json);
    return;
}




// echo $html;
// $dompdf = new Dompdf();
// // $dompdf->set_paper("A3");
// // $options = $dompdf->getOptions();
// // $options->setIsHtml5ParserEnabled(true);
// // $dompdf->setOptions($options);
// //Criando o <a href="https://www.homehost.com.br/blog/criar-sites/caracteres-especiais-acentos-html/" >código HTML</a> que será transformado em pdf
// $dompdf->loadHtml($html);
// // $dompdf->
// //Define o tipo de papel de impressão (opcional)
// //tamanho (A4, A3, A2, etc)
// //oritenação do papel:'portrait' (em pé) ou 'landscape' (deitado)
// // $dompdf->setPaper('A4', 'portrait');

// // Vai renderizar o HTML como PDF
// $dompdf->render();

// // Saída do pdf para a renderização do navegador.
// //Coloca o nome que deseja que seja renderizado.
// // $dompdf->stream("relatorio.pdf", array(true)); 
// // header("Content-type:application/pdf");
// // header_remove(["Content-Disposition"]);
// $dompdf->stream("file.pdf",array("Attachment" => 0));
// // $dompdf->stream();

// // echo "herr";

// // It will be called downloaded.pdf
// // header("Content-Disposition:attachment;filename=downloaded.pdf");

// // The PDF source is in original.pdf
// // readfile("manual.pdf");