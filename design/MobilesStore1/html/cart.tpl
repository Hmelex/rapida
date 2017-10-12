{$meta_title = "Корзина" scope=parent}

<div id="page_title">
<p><a href="./">Главная</a> » Состояние корзины
<h1>{if $cart->purchases}В корзине {$cart->total_products} {$cart->total_products|plural:'товар':'товаров':'товара'}{else}Корзина пуста{/if}</h1>
</div>
<br />

{if $cart->purchases}
	<h1>Шаг 1: Проверьте состояние вашей корзины</h1>
	<form method="post" name="cart">

		<table id="purchases">
			{foreach from=$cart->purchases item=purchase}
			<tr>
				<td class="image">
				{$image = $purchase->product->images|first}
				{if $image}<a href="products/{$purchase->product->url}"><img src="{$image->filename|resize:50:50}" alt="{$product->name|escape}"></a>{/if}
				</td>
				<td class="name"><a href="products/{$purchase->product->url}">{$purchase->product->name|escape}</a> <span class='color'>{$purchase->variant->name|escape}</span></td>
				<td class="price">{($purchase->variant->price)|convert} {$currency->sign}</td>
				<td class="amount" style='width:40px;'>
					<select name="amounts[{$purchase->variant->id}]" onchange="document.cart.submit();">
						{section name=amounts start=1 loop=$purchase->variant->stock+1 step=1}
						<option value="{$smarty.section.amounts.index}" {if $purchase->amount==$smarty.section.amounts.index}selected{/if}>{$smarty.section.amounts.index} {$settings->units}</option>
						{/section}
					</select>
				</td>
				<td class="price">{($purchase->variant->price*$purchase->amount)|convert}&nbsp;{$currency->sign}</td>
				<td class="remove"><a href="cart/remove/{$purchase->variant->id}"><img src="design/{$settings->theme}/images/bg/delete.png" title="Удалить из корзины" alt="Удалить из корзины"></a></td>
			</tr>
			{/foreach}

			{if $user->discount}<tr><th class="price" colspan="6">Скидка: {$user->discount}&nbsp;%</th></tr>{/if}
			<!-- www.Simpla-Template.ru / Oформление великолепных интернет магазинов. E-mail:help@simpla-template.ru | Skype:SimplaTemplate /-->
			{if $coupon_request}
				<tr>
					<th class="name" colspan="6" style='padding:0;'>
					<div class="form" style='text-align:left;'>
					<h2>Код подарочного купона</h2>
					{if $coupon_error}<div class="message_error">{if $coupon_error == 'invalid'}Купон недействителен или указан неверно!{/if}</div>{/if}
					<input type="text" name="coupon_code" value="{$cart->coupon->code|escape}">
					<input class="button right" type="button" name="apply_coupon"  value="Применить купон" onclick="document.cart.submit();">
					{if $cart->coupon->min_order_price>0}<p><br /><b>Поздравляем!</b><br />Купон '{$cart->coupon->code|escape}' действует для заказов, стоимостью от {$cart->coupon->min_order_price|convert} {$currency->sign}</p>{/if}
					{if $cart->coupon_discount>0}<h2 class="color">Ваша скидка: {$cart->coupon_discount|convert}&nbsp;{$currency->sign} ({$cart->coupon->value|convert}%)</h2>{/if}
					</div>
					</th>
				</tr>
				{literal}
				<script>
				$("input[name='coupon_code']").keypress(function(event){
					if(event.keyCode == 13){
						$("input[name='name']").attr('data-format', '');
						$("input[name='email']").attr('data-format', '');
						document.cart.submit();
					}
				});
				</script>
				{/literal}
			{/if}
			<tr><th class="price color" colspan="6">Итого	{$cart->total_price|convert}&nbsp;{$currency->sign}</th></tr>
		</table>


	{* Доставка *}
	{if $deliveries}
		<h1>Шаг 2: Выберите способ доставки:</h1>
		<ul id="deliveries">
			{foreach $deliveries as $delivery}
			<li>
			<div class="checkbox"><input type="radio" name="delivery_id" value="{$delivery->id}" {if $delivery_id==$delivery->id}checked{elseif $delivery@first}checked{/if} id="deliveries_{$delivery->id}"></div>
			<h3>
				<label for="deliveries_{$delivery->id}">
				{$delivery->name}
				{if $cart->total_price < $delivery->free_from && $delivery->price>0}({$delivery->price|convert}&nbsp;{$currency->sign})
				{elseif $cart->total_price >= $delivery->free_from}(бесплатно)
				{/if}
				</label>
			</h3>
			<div class="description">{$delivery->description}</div>
			</li>
			{/foreach}
		</ul>
	{/if}

	<h1>Шаг 3: Укажите адрес получателя, контакты и пожелания по заказу</h1>
	<div class="form cart_form">
		{if $error}
		<div class="message_error">
			{if $error == 'empty_name'}Введите имя{/if}
			{if $error == 'empty_email'}Введите email{/if}
			{if $error == 'captcha'}Капча введена неверно{/if}
		</div>
		{/if}
		<label>Имя, фамилия</label>
		<input name="name" type="text" value="{$name|escape}" data-format=".+" data-notice="Введите имя"/>
		<label>Email</label>
		<input name="email" type="text" value="{$email|escape}" data-format="email" data-notice="Введите email" />
		<label>Телефон</label>
		<input name="phone" type="text" value="{$phone|escape}" />
		<label>Адрес доставки</label>
		<input name="address" type="text" value="{$address|escape}"/>
		<label>Комментарий или Ваше пожелание к этому заказу</label>
		<textarea name="comment" id="order_comment">{$comment|escape}</textarea>
		<label>Введите число с картинки</label>
		<div class="captcha"><img src="captcha/image.php?{math equation='rand(10,10000)'}" alt='captcha'/></div>
		<input class="input_captcha" id="comment_captcha" type="text" name="captcha_code" value="" data-format="\d\d\d\d" data-notice="Введите капчу" maxlength="4"/>
		<input type="submit" name="checkout" class="button right" value="Оформить заказ">
		</div>
	</form>
{else}В корзине нет товаров{/if}