{* Шаблон корзины *}

{$meta_title = "Корзина" scope=root}

<h1>
{if $cart['purchases']}В корзине {$cart['total_products']} {$cart['total_products']|plural:'товар':'товаров':'товара'}
{else}Корзина пуста{/if}
</h1>

{if $cart['purchases']}
<form method="post" name="cart">

{* Список покупок *}
<table id="purchases">

{foreach $cart['purchases'] as $purchase}
<tr varid="{$purchase['variants'][0]['id']}">
	{* Изображение товара *}
	<td class="image">
		{$url = $purchase['product']['trans']}
		{$image = $purchase['product']['image']}
		{$image_id = $purchase['product']['image_id']}
		{$pname = $purchase['product']['name']}
		{if $image}
		<a href="/products/{$url}"><img src="{$image|resize:products:image_id:50:50}" alt="{$pname|escape}"></a>
		{/if}
	</td>
	
	{* Название товара *}
	<td class="name">
		<a href="/products/{$url}">{$pname|escape}</a>
		{$purchase['variants'][0]['name']|escape}
	</td>

	{* Цена за единицу *}
	<td class="price">
		{($purchase['variants'][0]['price'])|convert} {$currency['sign']}
	</td>

	{* Количество *}
	<td class="amount">
		<select name="amounts[{$purchase['variants'][0]['id']}]" onchange="document.cart.submit();">
			{$loop = min($purchase['variants'][0]['stock'], 100)}
			{section name=amounts start=1 loop=$loop step=1}
			<option value="{$smarty.section.amounts.index}" {if $purchase['amount']==$smarty.section.amounts.index}selected{/if}>{$smarty.section.amounts.index} {$settings->units}</option>
			{/section}
		</select>
	</td>

	{* Цена *}
	<td class="price">
		{($purchase['variants'][0]['price']*$purchase['amount'])|convert}&nbsp;{$currency['sign']}
	</td>
	
	{* Удалить из корзины *}
	<td class="remove">
		<a href="#">
		<img src="/design/{$settings->theme}/images/delete.png" title="Удалить из корзины" alt="Удалить из корзины">
		</a>
	</td>
			
</tr>
{/foreach}
{if isset($user['discount'])}
<tr>
	<th class="image"></th>
	<th class="name">скидка</th>
	<th class="price"></th>
	<th class="amount"></th>
	<th class="price">
		{$user['discount']}&nbsp;%
	</th>
	<th class="remove"></th>
</tr>
{/if}
{if $coupon_request}
<tr class="coupon">
	<th class="image"></th>
	<th class="name" colspan="3">Код купона или подарочного ваучера
		{if $coupon_error}
		<div class="message_error">
			{if $coupon_error == 'invalid'}Купон недействителен{/if}
		</div>
		{/if}
	
		<div>
		<input type="text" name="coupon_code" value="{$cart['coupon']['code']|escape}" class="coupon_code">
		</div>
		{if $cart['coupon']['min_order_price']>0}(купон {$cart['coupon']['code']|escape} действует для заказов от {$cart['coupon']['min_order_price']|convert} {$currency['sign']}){/if}
		<div>
		<input type="button" name="apply_coupon"  value="Применить купон" onclick="document.cart.submit();">
		</div>
	</th>
	<th class="price">
		{if $cart['coupon_discount']>0}
		&minus;{$cart['coupon_discount']|convert}&nbsp;{$currency['sign']}
		{/if}
	</th>
	<th class="remove"></th>
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

<tr>
	<th class="image"></th>
	<th class="name"></th>
	<th class="price" colspan="4">
		Итого
		{$cart['total_price']|convert}&nbsp;{$currency['sign']}
	</th>
</tr>
</table>

{* Связанные товары *}
{*
{if $related_products}
<h2>Так же советуем посмотреть</h2>
<!-- Список каталога товаров-->
<ul class="tiny_products">
	{foreach $related_products as $product}
	<!-- Товар-->
	<li class="product">
		
		<!-- Фото товара -->
		{if $product['image']}
		{$image = $product['image']}
		{$image_id = $product['image_id']}
		<div class="image">
			<a href="products/{$product['trans']}"><img src="{$product['image']resize:products:$image_id:200:200}" alt="{$product['name|escape']}"/></a>
		</div>
		{/if}
		<!-- Фото товара (The End) -->

		<!-- Название товара -->
		<h3><a data-product="{$product['id']}" href="products/{$product['trans']}">{$product['name|escape']}</a></h3>
		<!-- Название товара (The End) -->

		{if $product['variants|count'] > 0}
		<!-- Выбор варианта товара -->
			<table>
			{foreach $product['variants'] as $v}
			<tr class="variant">
				<td>
					{if $v['name']}<label class="variant_name" for="related_{$v['id']}">{$v['name']}</label>{/if}
				</td>
				<td>
					{if $v['old_price'] > 0}<span class="old_price">{$v['old_price']|convert}</span>{/if}
					<span class="price">{$v['price']|convert} <span class="currency">{$currency['sign']|escape}</span></span>
				</td>
				<td>
					<a href="#">в корзину</a>
				</td>
			</tr>
			{/foreach}
			</table>
		<!-- Выбор варианта товара (The End) -->
		{else}
			Нет в наличии
		{/if}


	</li>
	<!-- Товар (The End)-->
	{/foreach}
</ul>
{/if}
*}

{* Доставка *}
{if $deliveries}
<h2>Выберите способ доставки:</h2>
<ul id="deliveries">
	{foreach $deliveries as $delivery}
	<li>
		<div class="checkbox">
			<input type="radio" name="delivery_id" value="{$delivery['id']}" {if $delivery_id==$delivery['id']}checked{elseif $delivery@first}checked{/if} id="deliveries_{$delivery['id']}">
		</div>
		
			<h3>
			<label for="deliveries_{$delivery['id']}">
			{$delivery['name']}
			{if $cart['total_price'] < $delivery['free_from'] && $delivery['price']>0}
				({$delivery['price']|convert}&nbsp;{$currency['sign']})
			{elseif $cart['total_price'] >= $delivery['free_from']}
				(бесплатно)
			{/if}
			</label>
			</h3>
			<div class="description">
			{$delivery['description']}
			</div>
	</li>
	{/foreach}
</ul>
{/if}
    
<h2>Адрес получателя</h2>
	
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

	<label>Комментарий к&nbsp;заказу</label>
	<textarea name="comment" id="order_comment">{$comment|escape}</textarea>
	
	<div class="captcha"><img src="captcha/image.php?{math equation='rand(10,10000)'}" alt='captcha'/></div> 
	<input class="input_captcha" id="comment_captcha" type="text" name="captcha_code" value="" data-format="\d\d\d\d" data-notice="Введите капчу"/>
	
	<input type="submit" name="checkout" class="button" value="Оформить заказ">
	</div>
   
</form>
{else}
  В корзине нет товаров
{/if}

{literal}
<script>
"use strict";

function searchdel_item(e){
	"use strict";

	if(e.getAttribute('varid') === null){
		searchdel_item(e.parentNode);

	}else if ( e.getAttribute('varid') !== null ) {
		console.log("found: " + e.getAttribute('varid') );
		delete_item(e);

	}
}

function delete_item(el){
	"use strict";
	let tr = el;
	let id;
	if(tr.getAttribute('varid') !== undefined){
		id = tr.getAttribute('varid');
	}else {
		console.log(tr);
		console.log('unable to find varid');
	}

	let req = {
		class: 'cart',
		method: 'delete_item',
		args: {
			'id': id
		}
	};
	
	apiAjax(req, function(a){
		console.log(a);
		tr.remove();
	});
return true;
}

document.addEventListener("DOMContentLoaded", ready);

function ready(){
	"use strict";
	let col = document.querySelectorAll(".remove > a");
live('click', col, function(a){
	a.preventDefault();
	console.log(a.target);
	searchdel_item(a.target);
	});
}

</script>
{/literal}
