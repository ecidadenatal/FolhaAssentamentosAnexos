<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

db_postmemory($HTTP_POST_VARS);

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
$clrotulo = new rotulocampo;

$oPlugin       = new Plugin ( null, 'AssentamentoAnexos' );
$aConfiguracao = PluginService::getPluginConfig ( $oPlugin );

$sExtensoesSuportadas    = $aConfiguracao["ExtensoesSuportadas"];
$nTamanhoMaximo          = $aConfiguracao["TamanhoMaximo"];
$nEscalaAltura           = $aConfiguracao["EscalaAltura"];
$nEscalaLargura          = $aConfiguracao["EscalaLargura"];
$nEscalaAlturaThumbnail  = $aConfiguracao["EscalaAlturaThumbnail"];
$nEscalaLarguraThumbnail = $aConfiguracao["EscalaLarguraThumbnail"];
$sDiretorio              = $aConfiguracao["Diretorio"];

$nTamanhoMaximoBytes     = round( ($nTamanhoMaximo * 1048576) ,2);


if (isset($arquivo)) {

  try {	
  	
  	db_inicio_transacao();
  	
  	$sNomeArquivo           = $_FILES["arquivo"]["name"];
    $sNomeArquivoTemporario = $_FILES["arquivo"]["tmp_name"];
    $sExtensao              = strtolower ( pathinfo ( $sNomeArquivo, PATHINFO_EXTENSION ));
    $sMimeType              = $_FILES["arquivo"]["type"];
    $nTamanhoArquivo        = round($_FILES["arquivo"]["size"], 2);
  
    $sNomeArquivoNovo       = "anexo_assentamento_{$iAssentamento}_".date("Ymdhis").".{$sExtensao}";
    $sNomeArquivoThumbnail  = implode(explode(".",$sNomeArquivoNovo), "_thumbnail.");

    $sDestinoArquivo          = "{$sDiretorio}/{$sNomeArquivoNovo}";
    $sDestinoArquivoThumbnail = "{$sDiretorio}/{$sNomeArquivoThumbnail}";
    
    //validamos a extensão do arquivo
    if (!strstr($sExtensoesSuportadas, $sExtensao) ) {
      throw new Exception("Arquivo com extensão não suportada");  	
    }
    
    //Redimensiona o arquivo e gera o thumbnail, após verifica se o tamanho é menor que o limite
    if ($nTamanhoArquivo > $nTamanhoMaximoBytes) {
      throw new Exception("Tamanho do arquivo ultrapassa o limite permitido.");
    }
    
    // Faz um upload do arquivo para o local especificado
    if (!move_uploaded_file($sNomeArquivoTemporario,$sDestinoArquivo)) {
      throw new Exception("Erro ao enviar arquivo.");
    }
    
   
    // Resize da imagem
    if ($sExtensao != "pdf") {
    	
      switch($sExtensao) {
      	case "jpg":
      	case "jpeg":
      		$Imagem = imagecreatefromjpeg($sDestinoArquivo);
      	    break;
      	case "png":
      		$Imagem = imagecreatefrompng($sDestinoArquivo);
      		break;
      	case "gif":
      		$Imagem = imagecreatefromgif($sDestinoArquivo);
      		break;
      }
      
      if (!isset($Imagem)) {
        throw new Exception("[1] Erro gerando imagem");	
      }
      
      $nLarguraImagem   = imagesx($Imagem);
      $nAlturaImagem    = imagesy($Imagem);
      $nEscalaImagem    = min($nEscalaLargura/$nLarguraImagem, $nEscalaAltura/$nAlturaImagem);
      $nEscalaThumbnail = min($nEscalaLarguraThumbnail/$nLarguraImagem, $nEscalaAlturaThumbnail/$nAlturaImagem);
      
      // Se a imagem é maior que o permitido, encolhe ela!
      if ($nEscalaImagem < 1) {
      	
      	$nNovaLarguraImagem = floor($nEscalaImagem * $nLarguraImagem);
      	$nNovaAlturaImagem  = floor($nEscalaImagem * $nAlturaImagem);
      	
      	$nNovaLarguraThumbnail = floor($nEscalaThumbnail * $nLarguraImagem);
      	$nNovaAlturaThumbnail  = floor($nEscalaThumbnail * $nAlturaImagem);
      	
      	$NovaImagem      = imagecreatetruecolor($nNovaLarguraImagem,    $nNovaAlturaImagem);
      	$ImagemThumbnail = imagecreatetruecolor($nNovaLarguraThumbnail, $nNovaAlturaThumbnail);
      	if (!$NovaImagem || !$ImagemThumbnail) {
      	  throw new Exception("[2] Erro gerando nova imagem");
      	}
      	
      	$Resize1 = imagecopyresized($NovaImagem,      $Imagem, 0, 0, 0, 0, $nNovaLarguraImagem,    $nNovaAlturaImagem,    $nLarguraImagem, $nAlturaImagem);
      	$Resize2 = imagecopyresized($ImagemThumbnail, $Imagem, 0, 0, 0, 0, $nNovaLarguraThumbnail, $nNovaAlturaThumbnail, $nLarguraImagem, $nAlturaImagem);
      	if (!$Resize1 || !$Resize2) {
      		throw new Exception("[3] Erro redimensionando imagem");
      	}
      	
      	$Output1 = imagejpeg($NovaImagem, $sDestinoArquivo, 100);
      	$Output2 = imagejpeg($ImagemThumbnail, $sDestinoArquivoThumbnail, 100);
      	if (!$Output1 || !$Output2) {
      		throw new Exception("[3] Erro exportando imagem redimensionada");
      	}
      	
      	imagedestroy($Imagem);
      	
      	unset($NovaImagem);
      	unset($ImagemThumbnail);
      	
      	unset($Resize1);
      	unset($Resize2);
      	
      	unset($Output1);
      	unset($Output2);
      	
      	flush();
      }
    }
    
    //cadastramos os dados nas tabelas
    $oDaoAssentamentoAnexos = db_utils::getDao("assentamentoanexos");
    $oDaoAssentamentoAnexos->assentamento        = $iAssentamento;
    $oDaoAssentamentoAnexos->arquivo             = $sNomeArquivoNovo;
    $oDaoAssentamentoAnexos->arquivooriginal     = $sNomeArquivo;
    $oDaoAssentamentoAnexos->caminhoarquivo      = $sDestinoArquivo;
    $oDaoAssentamentoAnexos->data                = date("Y-m-d");
    $oDaoAssentamentoAnexos->ativo               = "t";
    $oDaoAssentamentoAnexos->incluir(null);
    if ($oDaoAssentamentoAnexos->erro_status == "0") {
      throw new Exception("Erro incluindo vinculo entre o anexo e o assentamento.\n".$oDaoAssentamentoAnexos->erro_msg);	
    }
    
    db_msgbox("Operação realizada com sucesso!");
    db_fim_transacao();
    
  } catch (Exception $oErro) {
  	
  	unset($sDestinoArquivo);
  	db_fim_transacao(true);
  	
  	db_msgbox($oErro->getMessage());
  	
  }
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?
db_app::load("scripts.js");
db_app::load("prototype.js");
db_app::load("datagrid.widget.js");
db_app::load("strings.js");
db_app::load("grid.style.css");
db_app::load("estilos.css");
db_app::load("AjaxRequest.js");
?>
<script>
function js_testacampo(){
  if(document.form1.arquivo.value != ""){
    document.form1.submit();
  }else{
    alert("Informe o arquivo.");
  }
}
</script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
<br>
<form name="form1" id='form1' method="post" action="" enctype="multipart/form-data">
<fieldset style="width: 80%">
 <legend>Anexos</legend>
 <table width="100%">
  <tr>
   <td align="center"> 
    <fieldset>
      <legend><b>Anexar Arquivos</b></legend>
      <br>
        <table border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
          <tr>
            <td nowrap title="Arquivo">
              <b>Arquivo:</b>
            </td>
            <td> 
        	 <?
           	  db_input("arquivo",30,0,true,"file",1);
        	 ?>
            </td>
          </tr>
        </table>
      <input name="Enviar" type="submit" id="enviar" value="Enviar" onclick="js_testacampo();">
    </fieldset>
   </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Arquivos Anexos</legend>
        <table width="100%">
          <tr>
            <td id="gridAnexos"></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>  
 </table> 
</fieldset>
<input type="hidden" value="<?=$iAssentamento?>" id="assentamento" name="assentamento">
</form>
</center>
</body>
</html>
<script>
  var sUrlRPC = "rec1_assentamentoAnexos.RPC.php";

  gridAnexos              = new DBGrid("gridAnexos");
  gridAnexos.nameInstance = "gridAnexos";
  gridAnexos.setCellWidth(new Array("10%","65%", "10%", "10%", "5%"));
  gridAnexos.setCellAlign(new Array("center", "left","center", "center","center"));
  gridAnexos.setHeader(new Array("Sequencial","Arquivo","Data","Download","Ativo"));
  gridAnexos.show(document.getElementById('gridAnexos'));

  js_buscaDadosGrid();
    
  function js_buscaDadosGrid() {
	  
    var oParametros = new Object();
    oParametros.exec = "getAnexos";
    oParametros.iAssentamento = $F("assentamento");
    
    var oParametros = js_objectToJson(oParametros),
        oAjax  = new Ajax.Request(sUrlRPC, 
      	                        { 
                                    method    : 'post',
                                    parameters: 'json='+oParametros,
                                    onComplete: js_preencheDadosGrid 
                                  }
                                 );
  }
  
  function js_preencheDadosGrid(oAjax) { 

  	var oRetorno = eval("("+oAjax.responseText+")");

    gridAnexos.clearAll(true);  
    if (oRetorno.iStatus == 1) {

    	for (iArquivo = 0; iArquivo < oRetorno.oDadosAnexos.length; iArquivo ++) {

  	  	  var iSequencial = oRetorno.oDadosAnexos[iArquivo].sequencial;
  	  	  var sArquivo    = oRetorno.oDadosAnexos[iArquivo].arquivo;
  	  	  var dData       = js_formatar(oRetorno.oDadosAnexos[iArquivo].data, 'd');	   
  	  	  var iSituacao   = oRetorno.oDadosAnexos[iArquivo].ativo;   	
  	  	  
  	  	  var aLinha  = new Array();
          aLinha[0]  = iSequencial
          aLinha[1]  = sArquivo;
          aLinha[2]  = dData;
          aLinha[3]  = '<input type="button" value="Dowload" onclick="js_downloadAnexo(\''+oRetorno.oDadosAnexos[iArquivo].caminhoarquivo+'\')">';
          aLinha[4]  = "<input type='checkbox' name='anexo_"+iSequencial+"'";
          if (iSituacao=='t') {
            aLinha[4] += " checked onclick='js_alterarSituacaoAnexo("+iSequencial+",\"f\")' >";
          } else {
        	aLinha[4] += " onclick='js_alterarSituacaoAnexo("+iSequencial+",\"t\")' >";
          }       	    
          gridAnexos.addRow(aLinha);               
    	}  
    }
  	gridAnexos.renderRows();

  }

  function js_downloadAnexo(sArquivo) {

	 if (!confirm('Deseja realizar o Download do Anexo?')) {
	   return false;
	 }
	   
	 window.open("db_download.php?arquivo="+sArquivo);
  } 

  function js_alterarSituacaoAnexo(iAnexo, sSituacao) {

	  var oParametros = new Object();
	  oParametros.exec = "alteraSituacaoAnexo";
	  oParametros.iSequencial   = iAnexo;
	  oParametros.sAtivo        = sSituacao;
	  
	  var oParametros = js_objectToJson(oParametros),
	      oAjax  = new Ajax.Request(sUrlRPC, 
	    	                        { 
	                                  method    : 'post',
	                                  parameters: 'json='+oParametros,
	                                  onComplete: js_buscaDadosGrid 
	                                }
	                               );
	    
  } 	  
</script>