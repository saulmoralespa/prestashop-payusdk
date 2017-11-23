<div class="row">
	<div class="col-xs-12 col-md-12">
		<p class="payment_module">
			<a class="bankwire" 
				 style="background: url({$this_path_bw}boton.png) 10px 5px no-repeat #fbfbfb;padding-left: 222px;" 
				 href="{$link->getModuleLink('payusdk', 'payment')|escape:'html'}" 
				 title="{l s='Pague atráves de payU de manera segura.' mod='payusdk'}">
				{l s='payU' mod='payusdk'}&nbsp;
				<span style="font-size: 14px;">
					{l s='(Pague atráves de payU de manera segura).' mod='payusdk'}
				</span>
			</a>
		</p>
	</div>
</div>