<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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
require_once(modification("classes/db_assenta_classe.php"));

db_postmemory($HTTP_POST_VARS);

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
$classenta = new cl_assenta;
$oAssentamentoAnexo = db_utils::getDao("assentamentoanexos");

$oGet = db_utils::postMemory($_GET);

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
<style type="text/css">
 .divConteudo {
   
   position: absolute;
   text-align: justify; 
   width:"80%";
   background-color:#FFFFFF;
   padding: 10px;
   border: 1px solid #000000;
   
 }
</style>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<br><br>
<table border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  <tr> 
    <td align="center" valign="top">
      <fieldset>
        <legend> Assentamentos </legend> 
      <?
      $dbwhere = "";
      
      $repassa = array();
      if(isset($codMatri) && trim($codMatri) != ""){

        $repassa["codMatri"] = $codMatri;
	      $dbwhere             = " h16_regist = ".$codMatri;
      }
      
      if(isset($codAssen) && trim($codAssen) != ""){

        $repassa["codAssen"]  = $codAssen;
	      $dbwhere             .= " and h16_assent = ".$codAssen;
      }
      
      if(isset($dataIni) && trim($dataIni) != ""){

        $repassa["dataIni"] = $dataIni;
        
        if(isset($dataFim) && trim($dataFim) != "") {

          $repassa["dataFim"]  = $dataFim;
          $dbwhere            .= " and h16_dtconc between '".$dataIni."' and '".$dataFim."' ";
        } else {
	        $dbwhere.= " and h16_dtconc >= '".$dataIni."' ";
	      }
      }

      $sSqlAssentamento = $classenta->sql_query_tipo(null,"h16_codigo, h12_assent, h12_descr, h16_dtconc, h16_dtterm, h16_quant, h16_nrport, h16_anoato, h16_atofic, h16_histor","h16_dtconc desc",$dbwhere);
      $rsDados = $classenta->sql_record($sSqlAssentamento);
      ?>
      <table width="100%" border=0>
        <tr class="table_header">
          <td>Código</td>
          <td>Descrição</td>
          <td>Data Inicial</td>
          <td>Data Final</td>
          <td>Quant. dias</td>
          <td>Número ato</td>
          <td>Ano ato</td>
          <td>Tipo ato</td>
          <td>Histórico</td>
          <td>Anexos</td>
        </tr>
        <?php
          for ($iInd = 0; $iInd < $classenta->numrows; $iInd++) {
          	$oDados = db_utils::fieldsMemory($rsDados, $iInd);
          	
          	$sHtmlAnexos = "";
          	$sSqlAssentamentoAnexos = $oAssentamentoAnexo->sql_query(null, "*", "sequencial", "assentamento = {$oDados->h16_codigo}");
          	$rsDadosAssentamentoAnexos = $oAssentamentoAnexo->sql_record($sSqlAssentamentoAnexos);
          	if ($oAssentamentoAnexo->numrows > 0) {
          	
          		for ($iAnexo = 0; $iAnexo < $oAssentamentoAnexo->numrows; $iAnexo++) {
          		  $oDadosAnexo = db_utils::fieldsMemory($rsDadosAssentamentoAnexos, $iAnexo);
          		  
          		  if (file_exists($oDadosAnexo->caminhoarquivo)) {
          		  
          		    $aExtensao = explode(".",$oDadosAnexo->arquivo);
          		    if ($aExtensao[1] == "pdf") {
          		    	$sIcon = "<img src='./imagens/icon_pdf.jpg'>";
          		    } else {
          		    	$sIcon = "<img src='./imagens/icon_image.jpg'>";
          		    }
          		    $sHtmlAnexos .= "&nbsp;<a href='#' onclick=\"js_downloadAnexo('$oDadosAnexo->caminhoarquivo')\" title='{$oDados->arquivo}'>{$sIcon}</a>&nbsp;";
          		    
          		  }
          		  
          		}
          		
          	}
          	
          	echo "<tr class='normal'> 
 		            <td>$oDados->h12_assent</td>
                    <td>$oDados->h12_descr</td>
                    <td>".db_formatar($oDados->h16_dtconc, "d")."</td>
                    <td>".db_formatar($oDados->h16_dtterm, "d")."</td>
                    <td>$oDados->h16_quant</td>
                    <td>$oDados->h16_nrport</td>
                    <td>$oDados->h16_anoato</td>
                    <td>$oDados->h16_atofic</td>
                    <td style='cursor: help;'>
                      <div onmouseover=\"js_mostraHistorico('div_{$oDados->h12_assent}',true)\" onmouseout=\"js_mostraHistorico('div_{$oDados->h12_assent}',false)\">".substr($oDados->h16_histor, 0, 60).(strlen($oDados->h16_histor)>60?" ...":"")."</div>
                      <div id=\"div_{$oDados->h12_assent}\" class=\"divConteudo\" style=\"display: none\">{$oDados->h16_histor}</div>
                    </td>
                    <td> {$sHtmlAnexos} </td>
                  </tr>";
          }
        
        ?>
      </table>
      </fieldset>
     </td>
   </tr>
   <tr>
     <td colspan="2" align = "center"> 
       <input  name="emite2" id="emite2" type="button" value="Imprimir" onclick="js_emite();" >
     </td>
   </tr>
</table>
</body>
</html>
<script>
function js_mostraHistorico(Div, lMostra) {

	if (lMostra == false) {
	  document.getElementById(Div).style.display="none";
	} else {
	  document.getElementById(Div).style.display="";
	}
	
}

function js_emite(){

  var iMatricula = '<?=$codMatri?>';
  var iCodAssen  = '<?=$codAssen?>';
  var sDataIni   = '<?=$oGet->dataIni?>';
  var sDataFim   = '<? isset($oGet->dataFim) ? $oGet->dataFim : '' ?>';

  qry  = 'codMatri='+iMatricula;
  qry += '&codAssen='+iCodAssen;

  if ( sDataIni != '' ) {
    qry += '&dataIni='+sDataIni;
  }

  if ( sDataFim != '' ) {
    qry += '&dataFim='+sDataFim;
  }

  jan = window.open('rec2_consafastfunc002.php?'+qry,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
}

function js_downloadAnexo(sArquivo) {

	 if (!confirm('Deseja realizar o Download do Anexo?')) {
	   return false;
	 }
	   
	 window.open("db_download.php?arquivo="+sArquivo);
 }
</script>