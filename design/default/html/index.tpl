<!DOCTYPE html>
{*
	Общий вид страницы
	Этот шаблон отвечает за общий вид страниц без центрального блока.
*}
<html>
<head>
	{$unknownvariable}
	
	{if $unknownvariable}
	take me there hell
	{/if}
	
	<base href="{$config->root_url}/"/>
	<title>{$meta_title|escape}</title>
	
	{* Метатеги *}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="{$meta_description|escape}" />
	<meta name="keywords"    content="{$meta_keywords|escape}" />
	<meta name="viewport" content="width=1024"/>
	
	{* Канонический адрес страницы *}
	{if isset($canonical)}<link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}
	
	{* Стили *}
	{bender src="/design/{$settings->theme|escape}/css/reset.css"}
	{bender src="/design/{$settings->theme|escape}/css/style.css"}
	<link href="/design/{$settings->theme|escape}/images/favicon.ico" rel="icon"          type="image/x-icon"/>
	<link href="/design/{$settings->theme|escape}/images/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
	
	{* JQuery *}
<!--
	{bender src="/js/jquery/jquery.js"}
-->
	
<!--
	{bender src="/js/admintooltip/admintooltip.js"}
-->
<!--
	{bender src="/js/admintooltip/css/admintooltip.css"} 
-->
			

	{* функции для работы с api системы *}
	{bender src="/js/main.js"}           
<!--
	
	{* Аяксовая корзина *}
	{bender src="/design/{$settings->theme}/js/jquery-ui.min.js"}
	{bender src="/design/{$settings->theme}/js/ajax_cart.js"}
	{* js-проверка форм *}
	{bender src="/js/baloon/js/baloon.js"}
	{bender src="/js/baloon/css/baloon.css"} 
-->
	
<!--
	{* Автозаполнитель поиска *}
	{bender src="/js/autocomplete/jquery.autocomplete-min.js"}

	{* Увеличитель картинок *}

	{bender src="/js/fancybox/jquery.fancybox.pack.js"}
	{bender src="/js/fancybox/jquery.fancybox.css"}


-->

	{*сжатые стили*}		
	{bender output="/compiled/{$settings->theme}/combined.css"}


</head>
<body>

	<!-- Верхняя строка -->
	<div id="top_background">
	<div id="top">
	
		<!-- Меню -->
		<ul id="menu">
			{foreach $pages as $p}
				{* Выводим только страницы из первого меню *}
				{if $p['menu_id'] == 1}
				<li {if $page && $page['id'] == $p['id']}class="selected"{/if}>
					<a data-page="{$p['id']}" href="{$p['trans']}">{$p['name']|escape}</a>
				</li>
				{/if}
			{/foreach}
		</ul>
		<!-- Меню (The End) -->
	
		<!-- Корзина -->
		<div id="cart_informer">
			{* Обновляемая аяксом корзина должна быть в отдельном файле *}
			{include file='cart_informer.tpl'}
		</div>
		<!-- Корзина (The End)-->

		<!-- Вход пользователя -->
		<div id="account">
			{if $user}
				<span id="username">
					<a href="user">{$user['email']}</a>{if $group['discount']>0},
					ваша скидка &mdash; {$group['discount']}%{/if}
				</span>
				<a id="logout" href="login/logout">выйти</a>
			{else}
				<a id="register" href="register">Регистрация</a>
				<a id="login" href="login/login">Вход</a>
			{/if}
		</div>
		<!-- Вход пользователя (The End)-->

	</div>
	</div>
	<!-- Верхняя строка (The End)-->
	
	
	<!-- Шапка -->
	<div id="header">
		<div id="logo">
			<a href="/"><img src="/design/{$settings->theme|escape}/images/logo.png" title="{$settings->site_name|escape}" alt="{$settings->site_name|escape}"/></a>
		</div>	
		<div id="contact">
			<span id="phone">{$settings->phone}</span>
			<span id="email">{$settings->comment_email}</span>
			<div id="address">Москва, шоссе Энтузиастов 45/31, офис 453</div>
		</div>	
	</div>
	<!-- Шапка (The End)--> 


	<!-- Вся страница --> 
	<div id="main">
	
		<!-- Основная часть --> 
		<div id="content">
			{$content}
		</div>
		<!-- Основная часть (The End) --> 

		<div id="left">

			<!-- Поиск-->
			<div id="search">
				<form action="search">
					<input class="input_search" type="text" name="keyword" value="{$keyword|escape}" placeholder="Поиск товара"/>
					<input class="button_search" value="" type="submit" />
				</form>
			</div>
			<!-- Поиск (The End)-->

			
			<!-- Меню каталога -->
			<div id="catalog_menu">
					
			{* Рекурсивная функция вывода дерева категорий *}
			{function name=categories_tree}
			{if $categories}
			<ul>
			{foreach $categories as $c}
				{* Показываем только видимые категории *}
				{if $c['visible']}
					<li>
						{if $c['image']}<img src="" alt="{$c['name']|escape}">{/if}
						<a {if isset($category) && $category['id'] == $c['id']}class="selected"{/if} href="catalog/{$c['trans']}" data-category="{$c['id']}">{$c['name']|escape}</a>
						{if isset($c['subcategories'])}{categories_tree categories=$c['subcategories']}{/if}
					</li>
				{/if}
			{/foreach}
			</ul>
			{/if}
			{/function}
			{categories_tree categories=$categories}
			</div>
			<!-- Меню каталога (The End)-->		
	
			
			<!-- Все бренды -->
			{* Выбираем в переменную $all_brands все бренды *}
			{get_brands var=all_brands}
			{if $all_brands}
			<div id="all_brands">
				<h2>Все бренды:</h2>
				{foreach $all_brands as $b}	
					{if $b['image']}
					<a href="brands/{$b['trans']}"><img src="" alt="{$b['name']|escape}"></a>
					{else}
					<a href="brands/{$b['trans']}">{$b['name']}</a>
					{/if}
				{/foreach}
			</div>
			{/if}
			<!-- Все бренды (The End)-->

			<!-- Выбор валюты -->
			{* Выбор валюты только если их больше одной *}
			{if $currencies|count>1}
			<div id="currencies">
				<h2>Валюта</h2>
				<ul>
					{foreach $currencies as $c}
					{if $c['enabled']} 
					<li class="{if $c['id']==$currency['id']}selected{/if}"><a href='{url currency_id=$c['id']}'>{$c['name']|escape}</a></li>
					{/if}
					{/foreach}
				</ul>
			</div> 
			{/if}
			<!-- Выбор валюты (The End) -->	

			
			<!-- Просмотренные товары -->
			{get_browsed_products var=browsed_products limit=20}
			{if !empty($browsed_products)}
			
				<h2>Вы просматривали:</h2>
				<ul id="browsed_products">
				{foreach $browsed_products as $browsed_product}
					<li>
					{$url = $browsed_product['trans']}
					{$name = $browsed_product['name']}
					{$image = $browsed_product['image']}
					{$image_id = $browsed_product['image_id']}
					<a href="products/{$url}"><img src="{$image|resize:products:$image_id:50:50}" alt="{$name|escape}" title="{$name|escape}"></a>
					</li>
				{/foreach}
				</ul>
			{/if}
			<!-- Просмотренные товары (The End)-->
			
			
			<!-- Меню блога -->
			{* Выбираем в переменную $last_posts последние записи *}
			{get_posts var=last_posts limit=5}
			{if $last_posts}
			<div id="blog_menu">
				<h2>Новые записи в <a href="blog">блоге</a></h2>
				{foreach $last_posts as $post}
				<ul>
					<li data-post="{$post['id']}">{$post['date']|date} <a href="blog/{$post['trans']}">{$post['name']|escape}</a></li>
				</ul>
				{/foreach}
			</div>
			{/if}
			<!-- Меню блога  (The End) -->
			
		</div>			

	</div>
	<!-- Вся страница (The End)--> 
	
	<!-- Футер -->
	<div id="footer">
	</div>
	<!-- Футер (The End)--> 


	{*сжатые js*}		
	{bender output="compiled/{$settings->theme}/combined.js"}

	{literal}
	<style>
		.autocomplete-suggestions{
		background-color: #ffffff;
		overflow: hidden;
		border: 1px solid #e0e0e0;
		overflow-y: auto;
		}
		.autocomplete-suggestions .autocomplete-suggestion{cursor: default;}
		.autocomplete-suggestions .selected { background:#F0F0F0; }
		.autocomplete-suggestions div { padding:2px 5px; white-space:nowrap; }
		.autocomplete-suggestions strong { font-weight:normal; color:#3399FF; }
	</style>	
	<script>
	$(function() {
		//  Автозаполнитель поиска
		$(".input_search").autocomplete({
			serviceUrl:'ajax/search_products.php',
			minChars:1,
			noCache: false, 
			onSelect:
				function(suggestion){
					 $(".input_search").closest('form').submit();
				},
			formatResult:
				function(suggestion, currentValue){
					var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
					var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
	  				return (suggestion.data.image?"<img align=absmiddle src='"+suggestion.data.image+"'> ":'') + suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
				}	
		});
	});
	</script>

	<script>
	$(function() {
		// Раскраска строк характеристик
		$(".features li:even").addClass('even');

		// Зум картинок
		$("a.zoom").fancybox({
			prevEffect	: 'fade',
			nextEffect	: 'fade'});
		});
	</script>
	{/literal}


</body>
</html>
