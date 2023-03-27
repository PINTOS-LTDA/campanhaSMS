<!DOCTYPE html>
<head>
    <title>Campanha SMS - Cargas Entrega</title>

    <!-- Styles-->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href = "fontawesome-free-6.1.1-web/css/all.css" rel="stylesheet" />
    <!-- JavaScript -->
    <script type="text/javascript" src="jquery-3.2.1/dist/jquery.slim.min.js"></script>
    <script type="text/javascript" src="bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="exportCSV.js"></script>
    <style>
        body{
            background-color: mintcream;
        }
        .modal-backdrop{
            background-color: blue;
        }
        .painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1vh 0;
        }
        #lista {
            margin: 6vh 0;
        }
        img{
            width: 20%;
        }
        #footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 1vh 0;
        }
    </style>
    <script>
        function newSpreadsheet(){
            $('#loading').modal('show');
        }
    </script>
</head>
<body>
<div class="container">

<div class="modal fade" tabindex="-1" role="dialog" id="loading" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="modal-loading">
            <div class="modal-header align-items-center justify-content-center flex-column">
                <h3 class="modal-title font-weight-bold">Grupo PINTOS</h3>
		<h6 class="font-italic">Gerar Dados de Status para Entrega</h6>		
            </div>
            <form method="POST" action="<?php echo $PHP_SELF; ?>">
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01">Data Entrega</label>
                        </div>
                        <input name="dataEntrega" type="date" class="form-control" placeholder="digite o codigo carga" aria-label="carga">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect02">Carga</label>
                        </div>
                        <input name="carga" type="number" class="form-control" placeholder="digite o codigo carga" aria-label="carga">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect03">Turno Entrega</label>
                        </div>
                        <select class="custom-select" id="turno" name="turno">
                            <option value="0" selected="selected">Todos</option>
                            <option value="1">Manha</option>
                            <option value="2">Tarde</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-info"  value="Gerar!">
                </div>
            </form>
        </div>
    </div>
</div>

<section id="footer" class="col-md-12">
    ©<?php echo "2023 - ".date("Y");?> BY TI PINTOS LTDA. Todos os direitos reservados.
</section>

<?php

session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE); //retirar o erro undefined
date_default_timezone_set('America/Sao_Paulo');

if(!empty($_POST['dataEntrega']) || !empty($_POST['carga'])){

    if($_POST['dataEntrega'] && !$_POST['carga']){
        $DATAENTREGA = date('Ymd', strtotime(htmlspecialchars($_POST['dataEntrega']) ));
        $parametro = 'AND awnfr.dataEntrega = '.$DATAENTREGA;
    }elseif( $_POST['carga'] && !$_POST['dataEntrega']){
        $CARGA = htmlspecialchars($_POST['carga']);
        $parametro = 'AND awnfr.cargano = '.$CARGA;
    }else{
        $DATAENTREGA = date('Ymd', strtotime(htmlspecialchars($_POST['dataEntrega']) ));
        $CARGA = htmlspecialchars($_POST['carga']);
        $parametro = 'AND awnfr.dataEntrega = '.$DATAENTREGA.' AND awnfr.cargano = '.$CARGA;
    }
    $TURNO          = htmlspecialchars($_POST['turno'])?:1;
    $html   = '';
    $count  = 0;

        $link = mysqli_connect('172.16.4.53', 'eacadm', '', 'sqldados');
        if (!$link) {
             die('Nao foi possivel conectar ao Banco de Dados. Motivo: ' . mysqli_error());
         } else{ //echo "conectado";
         }

        $tabela1 = " create TEMPORARY table TABSMS_1
		SELECT
                    awnfr.custno                        AS codigo,
                    LPAD(C2.ddd, 2, 0)               	AS ddd,
                    C2.l1                         	AS celular,
                    SUBSTRING_INDEX(custp.name, ' ', 1) AS name, 
                    awnfr.cargano                       AS carga,
                    awnfr.dataEntrega,
                    awnfr.ordno                         AS pedido,
                    (
                    SELECT
                            CASE
                                WHEN remarks like '%m%' THEN 'M'
                                WHEN remarks like '%n%' THEN 'M'
                                WHEN remarks like '%t%' THEN 'T'
                            ELSE ''
                                END AS remarks
                        FROM
                                    sqldados.awnfrh
                        where
                                awnfrh.storenoNfr = awnfr.storenoNfr
                            AND awnfrh.xanoNfr = awnfr.xanoNfr  
                            AND awnfrh.pdvnoNfr = awnfr.pdvnoNfr
                            AND status = 6
                        ORDER BY
                            awnfrh.date DESC,
                            time DESC
                        limit 1   
                        ) as turno
                    FROM
                        sqldados.awnfr
                        INNER JOIN sqldados.custp 
                            ON awnfr.custno = no
                        LEFT JOIN sqldados.eord as P2
                            ON P2.storeno = awnfr.storenoNFr and P2.ordno = awnfr.ordno
                        LEFT JOIN sqldados.ctadd as C2
                            ON  P2.custno = C2.custno 
                            and P2.custno_addno  = C2.seqno
                    
                    WHERE   
                    statusCarga IN(0)
                    $parametro 
                    AND C2.l1   <> 0 
                    AND C2.ddd     <> 0 ";

        $dados = "
		SELECT
                        55              AS 'telefone-ddi',
                        TRIM(ddd)             AS 'telefone-ddd',
                        TRIM(celular)         AS 'telefone-numero',
                        TRIM(name)            AS 'nome',
                        TRIM(GROUP_CONCAT(pedido SEPARATOR '-'))    AS pedido,
                        TRIM(CASE
                            WHEN GROUP_CONCAT(turno) = 'M'   THEN 'Manha'
                            WHEN GROUP_CONCAT(turno) = 'M,M' THEN 'Manha'
                            WHEN GROUP_CONCAT(turno) = 'M,T' THEN 'Manha-Tarde'
                            WHEN GROUP_CONCAT(turno) = 'T,M' THEN 'Tarde-Manha'
                            WHEN GROUP_CONCAT(turno) = 'T'   THEN 'Tarde'
                            WHEN GROUP_CONCAT(turno) = 'T,T' THEN 'Tarde'
                        END)                     AS turno,
                        
                        TRIM(DATE_FORMAT(dataEntrega, '%d/%m/%Y'))     AS 'data'
                    FROM TABSMS_1
                    WHERE
                    CASE $TURNO
                            WHEN 1 THEN turno = 'M'
                            WHEN 2 THEN turno = 'T'
                        ELSE turno IN('M','T')
                    END
                    GROUP BY codigo";

	$result = mysqli_query($link,$tabela1) or die (mysqli_error());
        $resultDados = mysqli_query($link,$dados) or die (mysqli_error());

        while ($row = mysqli_fetch_array($resultDados,MYSQLI_NUM)){
            $html .= '<tr>';
            $html .= '<td>'.$row[0].'</td>';
            $html .= '<td>'.$row[1].'</td>'; 
            $html .= '<td>'.$row[2].'</td>';
            $html .= '<td>'.$row[3].'</td>';
            $html .= '<td>'.$row[4].'</td>';
            $html .= '<td>'.$row[5].'</td>';
            $html .= '<td>'.$row[6].'</td>';      
            $html .= '</tr>';     
            $count++;
        }

        mysql_close($link);

        echo
        '<div id="lista">
        <img src="imagens/logo.png"/>
        <table class="table table-striped table-sm" id="Tabela" >
            <thead class="thead-dark">
                <tr>
                    <th scope="col">telefone-ddi</th>
                    <th scope="col">telefone-ddd</th>
                    <th scope="col">telefone-numero</th>
                    <th scope="col">nome</th>
                    <th scope="col">pedido</th>
                    <th scope="col">turno</th>
                    <th scope="col">data</th>
                </tr>
            </thead>' 
        .$html
        .'<div class="painel">'
        .'<button onclick="newSpreadsheet()" class="btn btn-primary"><i class="fa-solid fa-file"> novo </i></button>'
        .'<div>Quantidade de SMS: <font size="+2">'.$count.'</font></div>'
        .'<button onclick="Exportar()" class="btn btn-primary"><i class="fa-solid fa-file-csv"> Export </i></button>'
        .'</div>';
      
        session_destroy();
}else{
    echo "
        <script>
            $('#loading').modal('show');
        </script>
        ";
}
