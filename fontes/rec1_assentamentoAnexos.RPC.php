<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBSeller Servicos de Informatica             
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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("std/db_stdClass.php"));

$oJson                  = new services_json();
$oParametros            = $oJson->decode(str_replace("\\","",$_POST["json"]));

$oAssentamentoAnexo     = new cl_assentamentoanexos();

$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = "";

db_inicio_transacao();
try {

  switch ($oParametros->exec) {

  	case "getAnexos" :
  		
  		$oRetorno->oDadosAnexos = null;
  		$sSqlAssentamentoAnexos = $oAssentamentoAnexo->sql_query(null, "*", "sequencial", "assentamento = {$oParametros->iAssentamento}");
  		$rsAssentamentoAnexos   = $oAssentamentoAnexo->sql_record($sSqlAssentamentoAnexos);
  		if ($oAssentamentoAnexo->numrows > 0) {
  		  $oRetorno->oDadosAnexos = db_utils::getCollectionByRecord($rsAssentamentoAnexos);
  		} else {
  		  $oRetorno->iStatus = 2;
  		}
  		 
  	break;

  	case "alteraSituacaoAnexo" :

  		$sSqlUpdate = "update plugins.assentamentoanexos 
  		                  set ativo = '{$oParametros->sAtivo}'   
  		                where sequencial = {$oParametros->iSequencial}";  
  		if (!db_query($sSqlUpdate)) {
  			throw new Exception("Erro ao alterar situaчуo do anexo.");
  		}
  			
  	break;

    default:
      throw new Exception("Nуo localizado case para execuчуo no RPC.");
  }
  
  db_fim_transacao();

} catch (Exception $eErro) {

  db_fim_transacao(true);
  $oRetorno->iStatus      = 2;
  $oRetorno->sMessage     = urlencode($eErro->getMessage());
}

echo $oJson->encode($oRetorno);
?>