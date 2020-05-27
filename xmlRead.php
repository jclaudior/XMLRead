<?php
include("connect.php");
if(isset($_FILES['xmls'])){
    $qtdXML = count($_FILES['xmls']['name']);
    $valorTotalImportado = 0;
    $volumeTotalImportado = 0;
    for($i = 0; $i < $qtdXML; $i++){
    
        if( $_FILES['xmls']['type'][$i]=="text/xml"){

            $nomeXML = $_FILES['xmls']['name'][$i];

            echo "$nomeXML <br>";
            $getTemp = $_FILES['xmls']['tmp_name'][$i];
            echo $getTemp."<br>";
            rename($getTemp, sys_get_temp_dir()."\\".$_FILES['xmls']['name'][$i]);
            $getTemp = sys_get_temp_dir()."\\".$_FILES['xmls']['name'][$i];
            echo $getTemp."<br>";
            $xmls[] = simplexml_load_file($getTemp);
            
            $produto = count($xmls[$i]->NFe->infNFe->det);
         
            //print_r($xmls[$i]->NFe->infNFe->det[0]);
            $valorTotal=0;
            $volumeTotal=0;

            $numeroNFe = $xmls[$i]->NFe->infNFe->ide->nNF;
            $serieNFe = $xmls[$i]->NFe->infNFe->ide->serie;
            $CNPJEmitente = $xmls[$i]->NFe->infNFe->emit->CNPJ;
            $chaveNFe = $xmls[$i]->protNFe->infProt->chNFe;
            $dataEmissão = substr($xmls[$i]->NFe->infNFe->ide->dhEmi, 0, 10);
            $CFOP = $xmls[$i]->NFe->infNFe->det[0]->prod->CFOP;


            echo "<b>Numero NFe: </b>".$numeroNFe." <b>Serie: </b>".$serieNFe."<br>";
            echo "<b>CNPJ Emitente: </b>".$CNPJEmitente."<br>";
            echo "<b>CHAVE NFE: </b>". $chaveNFe." ";
            echo "<b>Data de Emissão: </b>".$dataEmissão ."<br>";
            echo "<b>CFOP: </b>".$CFOP."<br>";

            $vlTotal = $xmls[$i]->NFe->infNFe->transp->vol->qVol;
            $plTotal = $xmls[$i]->NFe->infNFe->transp->vol->pesoL;
            $pbTotal = $xmls[$i]->NFe->infNFe->transp->vol->pesoB;
            $SJICMS = $xmls[$i]->NFe->infNFe->total->ICMSTot->vBC;
            $ICMS = $xmls[$i]->NFe->infNFe->total->ICMSTot->vICMS;
            
            $sql = "SELECT * FROM PRODNFE WHERE NUMERO_NFE = ? AND SERIE_NFE = ? AND CODCLI_NFE = ?";
            $has = $PDO->prepare($sql);
            $has->execute(array($numeroNFe, $serieNFe, 0));
            $has = $has->fetchAll();
            for($j=0; $j < $produto; $j++){
                $CodigoProd = $xmls[$i]->NFe->infNFe->det[$j]->prod->cProd;
                $descProd = $xmls[$i]->NFe->infNFe->det[$j]->prod->xProd;
                $qtd = $xmls[$i]->NFe->infNFe->det[$j]->prod->qCom;
                $vl = $xmls[$i]->NFe->infNFe->det[$j]->prod->vProd;
                $vlu = $xmls[$i]->NFe->infNFe->det[$j]->prod->vUnCom;
                $ALICOTA = $xmls[$i]->NFe->infNFe->det[$j]->imposto->ICMS->ICMS00->pICMS;
                $CFOP = $xmls[$i]->NFe->infNFe->det[$j]->prod->CFOP;

                if(count($has) <= 0){
                    try{
                        $sql = "INSERT INTO PRODNFE (PROD_NFE,PROD_SERIE,PROD_CLI,PROD_COD,PROD_DESC,PROD_QTD,PROD_VLU) VALUES(?,?,?,?,?,?,?)";
                        $ex = $PDO->prepare($sql);
                        $ex->execute(array($numeroNFe, $serieNFe, 0000,$CodigoProd,$descProd,$qtd,$vlu));
                    }catch(Exception $e){
                        echo "Erro gravar Produtos "+ $e->getMessage();
                    }
                }
                

                echo "Codigo:".$CodigoProd." Descrição:".$descProd." Quantidade:".$qtd.
                " Valor:".$vl."<br>";
                $valorTotal+=$vl;
            }

           

            echo "<br>Volume Total:".$vlTotal." Peso Liquido Total: ".$plTotal." Peso Bruto Total: ".$pbTotal." Valor Total: $valorTotal";
            echo "<br>SJICMS: ".$SJICMS." ICMS: ".$ICMS;
            echo "<br>ALICOTA ICMS: ".$ALICOTA ;
            
            
            //echo($xmls[$i]->NFe->infNFe->det[1]->prod->cProd);
            echo"<br>";
            $volumeTotalImportado += $vlTotal;
            $valorTotalImportado += $valorTotal;
        }
    }
    echo "<b>Volume Total Importado: $volumeTotalImportado Valor Total Importado: $valorTotalImportado";

    $dataAtual = Date("Y-m-d"); 
    echo $dataAtual;
    try{
        $sql = "INSERT INTO CADNFE VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $ex = $PDO->prepare($sql);
        $ex->execute(array($numeroNFe,$serieNFe,0000,$dataEmissão,$CFOP,$vlTotal,$plTotal,$pbTotal,$SJICMS,$ICMS,$ALICOTA,$valorTotal,$dataAtual,133,'s',000000,$chaveNFe,$nomeXML));
    }
    catch(Exception $e){
        echo "Erro ao gravar NFe "+ $e->getMessage();
    }

}
else{
    echo "Não tem XML";

}




?>