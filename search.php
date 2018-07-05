<?php header("Content-Type: text/html;  charset=pt_br.utf-8",true); ?>

<html>
	<title> CONSULTA LDAP by NOC </title>

<head>
	<link rel="icon" href="images/LOGO_TJPA.gif" type="image/x-icon" />
</head>


<style type="text/css">
.consultatj {
	color: #6AB5FF;
}
body {
	background-color: #E4E0D5;
}
.fontconsulta {
	color: #526B4E;
}
.fontconsulta {
	font-family: Andalus, AngsanaUPC;
	font-style: normal;
}
</style>

<body>

	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<div align="center">

	<table width="733" height="211" border="0">
    	<tr>
        	<td colspan="3" bgcolor="#526B4E">&nbsp;</td>
        </tr>
        <tr>
        	<td colspan="3" class="fontconsulta-white"><a href="javascript:history.back(-1)" title="Voltar"><img src="images/Logo.v2.jpg" width="733" height="158" border="0"></a></td>
        </tr>
        <tr>
        	<td colspan="3" bgcolor="#526B4E" class="fontconsulta-white">&nbsp;</td>
        </tr>
            <td width="109" class="fontconsulta-white">&nbsp;</td>
        <td width="621">

</html>

<?php 

// Log de acessos
#$data = "DATA: ".date('d/m/Y G:i:s',mktime(date('G'),date('i'),date('s'),date('m'),date('d'),date('Y')))." ";
#$ip = " -----> IP: ".$_SERVER["REMOTE_ADDR"];
#$linha = $data.$ip."\n";

#$log = fopen("/var/log/acesso.log", "a");
#$grava = fwrite($log, $linha);
#fclose($log);

include 'connect.php';

// Define um array dos atributos a serem exibidos
$attrs = array("displayname","givenname","sn","samaccountname","description","physicaldeliveryofficename","mail","proxyAddresses","whenCreated","useraccountcontrol","accountexpires","pwdlastset","lastLogon");

/* Recebe os dados da pesquisa na página html, e 
exclui, se digitado o caractere * do início e do fim da pesquisa para evitar erro. */
//$name = ltrim(rtrim($_POST['displayname'], "*"), "*");
$name = trim($_POST['displayname']);
//$login = ltrim(rtrim($_POST['samaccountname'], "*"), "*");
$login = trim($_POST['samaccountname']);
//$desc = ltrim(rtrim($_POST['description'], "*"), "*");
$desc = trim($_POST['description']);
//$office = ltrim(rtrim($_POST['physicaldeliveryofficename'], "*"), "*");
$office = trim($_POST['physicaldeliveryofficename']);

// Define o filtro da pesquisa
if (($name == "")AND($login == "")) {
	if (($desc == "")AND($office == "")) {
		echo "<p class=\"fontconsulta\">***** <b>ATENCAO:</b> Pelo menos 1 campo deve ser preenchido ***** </p>";  
		$desconectado = ldap_close($connect); /* Fecha a conexão caso nenhum campo tenha sido preenchido. */  
	} else {
		if ($desc == "") $desc = $office;
		else $office = $desc;
		$filter = "(&(&(&(!(objectClass=group))(!(objectClass=computer)))(!(objectClass=connectionpoint)))(|(description=*".$desc."*)(physicaldeliveryofficename=*".$office."*)))";
		$desconectado = 0;
		   }
} else {
	if (($desc == "")AND($office == "")) {
		if ($name == "") $name = $login;
		else $login = $name; 
		$filter = "(&(&(&(!(objectClass=group))(!(objectClass=computer)))(!(objectClass=connectionpoint)))(|(displayname=*".$name."*)(samaccountname=*".$login."*)))";
		$desconectado = 0;
	} else {
		if ($name == "") $name = $login;
		else $login = $name;
		if ($desc == "") $desc = $office;
		else $office = $desc; 
		$filter = "(&(&(&(!(objectClass=group))(!(objectClass=computer)))(!(objectClass=connectionpoint)))(&(|(displayname=*".$name."*)(samaccountname=*".$login."*))(|(description=*".$desc."*)(physicaldeliveryofficename=*".$office."*))))";
		$desconectado = 0;
		   }
}

if (!($desconectado)) {

	$search = @ldap_search($connect, $base_dn, $filter) or die ("ldap search failed"); /*Executa a pesquisa*/
	$entries = ldap_get_entries($connect, $search); /*Lê as entradas do resultado encontrado*/
	$number_returned = ldap_count_entries($connect,$search); /* Conta a quantidade de entradas retornadas na pesquisa*/

	echo "<h2 class=\"fontconsulta\">Resultado: $number_returned usuário(s).</h2>";
	if ($number_returned >= 2000) echo "<p class=\"fontconsulta\"><b>ATENÇÃO:</b> Alguns usuários foram omitidos. Restrinja mais a sua pesquisa<br>";

	if ($entries["count"] > 0) {
		for ($i=0; $i<$entries["count"]; $i++) {
			echo "<p class=\"fontconsulta\">---------------------------------------------------------------------------<br>";
			if (isset($entries[$i]["samaccountname"][0]))	echo "LOGIN: <b>".$entries[$i]["samaccountname"][0]."</b><br />";
			else echo "LOGIN: <br />";
			if (isset($entries[$i]["displayname"][0]))	echo "NOME COMPLETO: ".$entries[$i]["displayname"][0]."<br />";
			else echo "NOME COMPLETO: <br />";
			if (isset($entries[$i]["givenname"][0]))	echo "NOME: ".$entries[$i]["givenname"][0]."<br />";
			else echo "NOME: <b>NÃO PREENCHIDO </b> <br />";
			if (isset($entries[$i]["sn"][0]))	echo "SOBRENOME: ".$entries[$i]["sn"][0]."<br />";
			else echo "SOBRENOME: <b>NÃO PREENCHIDO </b><br />";
			if (isset($entries[$i]["description"][0]))	echo "DESCRIÇÃO: ".$entries[$i]["description"][0]."<br />";
			else echo "DESCRIÇÃO: <br />";
			if (isset($entries[$i]["physicaldeliveryofficename"][0]))	echo "LOTAÇÃO: ".$entries[$i]["physicaldeliveryofficename"][0]."<br />";
			else echo "LOTAÇÃO: <br />";

#			if (isset($entries[$i]["mail"][0]))	echo "E-MAIL: ".$entries[$i]["mail"][0]."<br />";
#			else echo "E-MAIL: <br />";


                        if ($entries[$i]["proxyaddresses"]["count"] > 0) {
				$skype = 0;
				$email = 0;
                                for ($j=0; $j<$entries[$i]["proxyaddresses"]["count"]; $j++) {
                                        $pxaddr = str_ireplace("%smtp%","",$entries[$i]["proxyaddresses"][$j]);
                                        $alias = str_word_count($pxaddr, 1, '@.0123456789');
                                        if (stripos($alias[0],"x") === false){
                                                if (strcasecmp($alias[0],"sip") != 0){
                                                        echo "EMAILS: ";
                                                        print_r($alias[1]);
                                                        echo "<br />";
							$email++;
                                                }
                                                else {
                                                        $skype++;
                                                        $endskype = ($alias[1]);
                                                }
                                        }
					if ($j == ($entries[$i]["proxyaddresses"]["count"])-1){
                                                if ($skype == 1){
                                                       	echo "SKYPE: ";
                                                       	print_r($endskype);
                                                       	echo "<br />";
							$skype++;
                                                }
                                        	
					}
                                }
				if ($email == 0) echo "E-MAILS: <b>NÃO POSSUI </b> <br />";
				if ($skype == 0) echo "SKYPE: <b>NÃO POSSUI </b> <br />";
                        }
                        else {
				echo "E-MAILS: <b>NÃO POSSUI </b> <br />";
				echo "SKYPE: <b>NÃO POSSUI </b> <br />";
			}


			if (isset($entries[$i]["whencreated"][0])){
				$data_conta=$entries[$i]["whencreated"][0]; 
        	       		$ano=substr($data_conta, 0, 4);
               			$mes=substr($data_conta, 4, 2);
                		$dia=substr($data_conta, 6, 2);
                		$hora=substr($data_conta, 8, 2)-3;
	                	$min=substr($data_conta, 10, 2);
        	        	$seg=substr($data_conta, 12, 2);
				echo "DATA DA CRIAÇÃO DA CONTA: ".date("d/m/Y G:i:s", mktime($hora,$min,$seg,$mes,$dia,$ano))."<br>";
			}
			if ($entries[$i]["pwdlastset"][0] > 0){
				$d=getdate(((@$entries[$i]["pwdlastset"][0] / 10000000) - 11644560000));
                        	$ano=$d["year"];
                        	$mes=$d["mon"];
                        	$dia=$d["mday"]+1;
                        	$hora=$d["hours"];
                        	$min=$d["minutes"];
                        	$seg=$d["seconds"];
                        	echo "ÚLTIMA TROCA DA SENHA: ".date("d/m/Y G:i:s", mktime($hora,$min,$seg,$mes,$dia,$ano))."<br>";
			}
                        if ($entries[$i]["lastlogontimestamp"][0] > 0){
                                echo "OBS: Usuário(a) já logou na rede pelo menos uma vez"."<br>";
				//$d=getdate(((@$entries[$i]["lastlogon"][0] / 10000000) - 11644560000));
                                //$ano=$d["year"];
                                //$mes=$d["mon"];
                                //$dia=$d["mday"]+1;
                                //$hora=$d["hours"];
                                //$min=$d["minutes"];
                                //$seg=$d["seconds"];
                                //echo "ULTIMO LOGON: ".date("d/m/Y G:i:s", mktime($hora,$min,$seg,$mes,$dia,$ano))."<br>";
			}
			else echo "OBS: Usuário(a) nunca logou na rede"."<br>";
			if ((@$entries[$i]["useraccountcontrol"][0] != 66050) and (@$entries[$i]["useraccountcontrol"][0] != 514)){
				if ((@$entries[$i]["useraccountcontrol"][0] != 66048) and (@$entries[$i]["useraccountcontrol"][0] != 66080) and (@$entries[$i]["useraccountcontrol"][0] != 544)){
					$d=getdate(((@$entries[$i]["pwdlastset"][0] / 10000000) - 11644560000));
   					$ano=$d["year"]; 
   					$mes=$d["mon"]; 
   					$dia=$d["mday"]; 
   					$hora=$d["hours"]; 
   					$min=$d["minutes"]; 
   					$seg=$d["seconds"]; 
						if ((mktime($hora,$min,$seg,$mes,$dia+90,$ano)) > time())
   							echo "SENHA VENCERA: ".date("d/m/Y G:i:s", mktime($hora,$min,$seg,$mes,$dia+90,$ano))."<br>";
						else 
							if ($entries[$i]["pwdlastset"][0] > 0)
								echo "<b>******** SENHA VENCIDA - DIA: ".date("d/m/Y G:i:s", mktime($hora,$min,$seg,$mes,$dia+90,$ano))." ********</b><br />";
							else
								echo "<b>******** NECESSÁRIO TROCAR SENHA NO LOGON ********</b><br />";
				} 
				if (@$entries[$i]["accountexpires"][0] != 0){
					if (((@$entries[$i]["accountexpires"][0] / 10000000) - 11644560000) < time()){
						echo "<b>******** CONTA DE REDE EXPIRADA ********</b><br />";
					}
				}
			}
			else
				echo "<b>******** USUÁRIO DESABILITADO ********</b><br />";
		}	
	} 
	echo "</p>";
	ldap_close($connect); /*Fecha a conexão*/

}

?>

<html>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

	<form action="index.html" method="_parente" class="fontconsulta">
		<p><input type="submit" value="Voltar" /></p>
	</form>

		</td>
    	</tr>
        <tr>
                <td colspan="3" bgcolor="#526B4E"><p align="center"><em><font color="white">Coordenadoria de Suporte Técnico<br>
                Serviço de Segurança e Sistemas Básicos (NOC)<br>Powered by Openshift</font></em><br>
          	</p></td>
        </tr>
	</table>

</div>
<div align="center"></div>
</body>
</html>
