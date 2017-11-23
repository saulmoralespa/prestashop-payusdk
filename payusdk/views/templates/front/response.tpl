
<link rel="stylesheet" href="{$css_dir}global.css" type="text/css" media="all">


	<center>
		<table id="pu-response" class="table-response" style="width: auto;">
			<tr align="center">
				<th colspan="2"><h1 class="md-h1">{l s='Datos De Compra' mod='payusdk'}</h1></th>
			</tr>
			<tr align="left">
				<td>{l s='Id de la Transaccion' mod='payusdk'}</td>
				<td>{$transactionId|escape:'htmlall':'UTF-8'}</td>
			</tr>		
			<tr align="left">
				<td>{l s='Referencia de compra' mod='payusdk'}</td>
				<td>{$referenceCode|escape:'htmlall':'UTF-8'}</td>
			</tr>			
			{if $pseBank!=null}
				<tr align="left">
					<td>{l s='Banco' mod='payusdk'}</td>
					<td>{$pseBank|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/if}
			<tr align="left">
				<td>{l s='Valor total' mod='payusdk'}</td>
				<td>${$value|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Moneda' mod='payusdk'}</td>
				<td>{$currency|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Description' mod='payusdk'}</td>
				<td>{$description|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Entidad' mod='payusdk'}</td>
				<td>{$lapPaymentMethod|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</table>
		<p/>
		<h1>{$messageApproved|escape:'htmlall':'UTF-8'}</h1>
	</center>
