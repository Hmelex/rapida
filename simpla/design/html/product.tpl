{capture name=tabs}
	<li class="active"><a href="{url module=ProductsAdmin category_id=$product['category_id'] return=null brand_id=null id=null}">Товары</a></li>
	{if isset($userperm['categories'])}<li><a href="?module=CategoriesAdmin">Категории</a></li>{/if}
	{if isset($userperm['brands'])}<li><a href="?module=BrandsAdmin">Бренды</a></li>{/if}
	{if isset($userperm['features'])}<li><a href="?module=FeaturesAdmin">Свойства</a></li>{/if}
{/capture}

{if $product['id']}
{$meta_title = $product['name'] scope=parent}
{else}
{$meta_title = 'Новый товар' scope=parent}
{/if}

{* Подключаем Tiny MCE *}
{include file='tinymce_init.tpl'}

{* On document load *}
{literal}
<script src="design/js/autocomplete/jquery.autocomplete-min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", ready);

	
	
function ready(){
	"use strict";
	// ссылка на добавление
	function add_link(e) {
	"use strict";
		let t = document.getElementById('category_tpl');
		let n = t.cloneNode(true);
		t.parentNode.insertBefore(n,t);
		n.removeAttribute('id');
		n.setAttribute('style','');
	}
	// ссылка на удаление
	function delete_link(e) {
	"use strict";
		let t = document.getElementById('category_tpl');
		let n = t.cloneNode(true);
		t.parentNode.insertBefore(n,t);
		n.removeAttribute('id');
		n.setAttribute('style','');
	}
	
	//включаем обработчик на все такие ссылки
	live('click', document.getElementsByClass('add'), add_link);
	live('click', document.getElementByClass('delete'), delete_link);




/* DragnDrop */
var holder = document.getElementById('holder'),
	product_id,
	tests = {
	  filereader: typeof FileReader != 'undefined',
	  dnd: 'draggable' in document.createElement('span'),
	  formdata: !!window.FormData,
	  progress: "upload" in new XMLHttpRequest
	}, 
	support = {
	  filereader: document.getElementById('filereader'),
	  formdata: document.getElementById('formdata'),
	  progress: document.getElementById('progress')
	},
	acceptedTypes = {
	  'image/png': true,
	  'image/jpeg': true,
	  'image/gif': true
	},
	progress = document.getElementById('uploadprogress'),
	fileupload = document.getElementById('upload');
	if(holder !== null){
		product_id = holder.getAttribute('product_id');
	} else {
		return false;
	}


"filereader formdata progress".split(' ').forEach(function (api) {
  if (tests[api] === false) {
	support[api].className = 'fail';
  } else {
	// FFS. I could have done el.hidden = true, but IE doesn't support
	// hidden, so I tried to create a polyfill that would extend the
	// Element.prototype, but then IE10 doesn't even give me access
	// to the Element object. Brilliant.
	support[api].className = 'hidden';
  }
});

function previewfile(file) {
  if (tests.filereader === true && acceptedTypes[file.type] === true) {
	var reader = new FileReader();
	reader.onload = function (event) {
	  var image = new Image();
	  image.src = event.target.result;
	  image.width = 100; // a fake resize
	  imagelist.appendChild(image);
	};

	reader.readAsDataURL(file);
  }  else {
	holder.innerHTML += '<p>Uploaded ' + file.name + ' ' + (file.size ? (file.size/1024|0) + 'K' : '');
	console.log(file);
  }
}

function readfiles(files) {
	//debugger;
	//console.log(files);
	var formData = tests.formdata ? new FormData() : null;
	for (var i = 0; i < files.length; i++) {
	  if (tests.formdata){
		   formData.append('file[]', files[i]);
	   }
		formData.append('product_id', product_id );
	}
	//console.log(formData);

	// now post a new XHR request
	if (tests.formdata) {
	  var xhr = new XMLHttpRequest();
	  xhr.open('POST', '/simpla/ajax/upload_image.php');
	  xhr.onload = function() {
		progress.value = progress.innerHTML = 100;
		for (var i = 0; i < files.length; i++) {
			previewfile(files[i]);
		}
	  };

	  if (tests.progress) {
		xhr.upload.onprogress = function (event) {
		  if (event.lengthComputable) {
			var complete = (event.loaded / event.total * 100 | 0);
			progress.value = progress.innerHTML = complete;
		  }
		}
	  }

	  xhr.send(formData);
	}
}

if (tests.dnd) { 
  holder.ondragover = function () { this.className = 'hover'; return false; };
  holder.ondragend = function () { this.className = ''; return false; };
  holder.ondrop = function (e) {
	this.className = '';
	e.preventDefault();
	readfiles(e.dataTransfer.files);
  }
} else {
	alert('else');
  fileupload.className = 'hidden';
  fileupload.querySelector('input').onchange = function () {
	readfiles(this.files);
  };
}
/* DragnDrop (The end) */
}


	

</script>

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
{/literal}



{if !$message_error && $message_success}
<!-- Системное сообщение -->
<div class="message message_success">
	{if $message_success}
	<span class="text">{if $message_success=='added'}Товар добавлен{elseif $message_success=='updated'}Товар изменен{else}{$message_success|escape}{/if}</span>
	{/if}
	
	<a class="link" target="_blank" href="../products/{$product['url']}">Открыть товар на сайте</a>
	
	<span class="share">		
		<a href="#" onClick='window.open("http://vkontakte.ru/share.php?url={$config->root_url|urlencode}/products/{$product['url']|urlencode}&title={$product['name']|urlencode}&description={$product['annotation']|urlencode}&image={$product['images'].0->filename|resize:1000:1000|urlencode}&noparse=true","displayWindow","width=700,height=400,left=250,top=170,status=no,toolbar=no,menubar=no");return false;'>
		<img src="{$config->root_url}/simpla/design/images/vk_icon.png" /></a>
		<a href="#" onClick='window.open("http://www.facebook.com/sharer.php?u={$config->root_url|urlencode}/products/{$product['url']|urlencode}","displayWindow","width=700,height=400,left=250,top=170,status=no,toolbar=no,menubar=no");return false;'>
		<img src="{$config->root_url}/simpla/design/images/facebook_icon.png" /></a>
		<a href="#" onClick='window.open("http://twitter.com/share?text={$product['name']|urlencode}&url={$config->root_url|urlencode}/products/{$product['url']|urlencode}&hashtags={$product['meta_keywords']|replace:' ':''|urlencode}","displayWindow","width=700,height=400,left=250,top=170,status=no,toolbar=no,menubar=no");return false;'>
		<img src="{$config->root_url}/simpla/design/images/twitter_icon.png" /></a>
	</span>
	
</div>
<!-- Системное сообщение (The End)-->

{elseif $message_error}
<!-- Системное сообщение -->
<div class="message message_error">
	<span class="text">{if $message_error=='url_exists'}Товар с таким адресом уже существует{elseif $message_error=='empty_name'}Введите название{else}{$message_error|escape}{/if}</span>
</div>
<!-- Системное сообщение (The End)-->
{/if}


<!-- Основная форма -->
<form method=post id=product enctype="multipart/form-data">
<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div id="name">
		<input class="name" name=name type="text" value="{$product['name']|escape}"/> 
		<input name=id type="hidden" value="{$product['id']|escape}"/> 
		<div class="checkbox">
			<input name=visible value='1' type="checkbox" id="active_checkbox" {if $product['visible']}checked{/if}/> <label for="active_checkbox">Активен</label>
		</div>
		<div class="checkbox">
			<input name=featured value="1" type="checkbox" id="featured_checkbox" {if $product['featured']}checked{/if}/> <label for="featured_checkbox">Рекомендуемый</label>
		</div>
	</div> 
	
	<div id="product_brand" {if !$brands}style='display:none;'{/if}>
		<label>Бренд</label>
		<select name="brand_id">
			<option value='0' {if !$product['brand_id']}selected{/if} brand_name=''>Не указан</option>
			{foreach $brands as $brand}
				<option value='{$brand['id']}' {if $product['brand_id'] == $brand['id']}selected{/if} brand_name='{$brand['name']|escape}'>{$brand['name']|escape}</option>
			{/foreach}
		</select>
	</div>
	
	
	<div id="product_categories" {if !$cats}style='display:none;'{/if}>
		<label>Категория</label>
		<div>
			<ul>
			{function name=category_select level=0}
			{foreach $categories as $cat}
					<option value="{$cat['id']}" {if $cat['id'] == $selected_id}selected{/if} category_name="{$cat['name']|escape}">{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$cat['name']|escape}</option>
					{category_select categories=$cat['subcategories'] selected_id=$selected_id  level=$level+1}
			{/foreach}
			{/function}
			{if isset($product['cats'])}
				{foreach $product['cats'] as $pc name=categories}
				<li>
					<select name="categories[]">
						{category_select categories=$cats selected_id=$pc['id']}
					</select>
				</li>
				{/foreach}
			{/if}
				<li id="category_tpl" class="icons">
					<select name="categories[]">
						{category_select categories=$cats selected_id=$pc['id']}
					</select>
					<a class="delete"></a>
				</li>
				<span class="add" id="add_category"><i class="dash_link">Дополнительная категория</i></span>
				<span {if !isset($product['cats'])}style='display:none;'{/if} class="delete"><i class="dash_link">Удалить</i></span>
			</ul>
		</div>
	</div>


	<!-- Варианты товара -->
	<div id="variants_block" {assign var=first_variant value=$product['variants']|@first}{if $product['variants']|@count <= 1 && !$first_variant['name']}class=single_variant{/if}>
		<ul id="header">
			<li class="variant_move"></li>
			<li class="variant_name">Название варианта</li>	
			<li class="variant_sku">Артикул</li>	
			<li class="variant_price">Цена, {$currency['sign']}</li>	
			<li class="variant_discount">Старая, {$currency['sign']}</li>	
			<li class="variant_amount">Кол-во</li>
		</ul>
		<div id="variants">
		{foreach $product['variants'] as $variant}
		<ul>
			<li class="variant_move"><div class="move_zone"></div></li>
			<li class="variant_name">      <input name="variants[id][]"            type="hidden" value="{$variant['id']|escape}" /><input name="variants[name][]" type="" value="{$variant['name']|escape}" /> <a class="del_variant" href=""><img src="design/images/cross-circle-frame.png" alt="" /></a></li>
			<li class="variant_sku">       <input name="variants[sku][]"           type="text"   value="{$variant['sku']|escape}" /></li>
			<li class="variant_price">     <input name="variants[price][]"         type="text"   value="{$variant['price']|escape}" /></li>
			<li class="variant_discount">  <input name="variants[compare_price][]" type="text"   value="{$variant['compare_price']|escape}" /></li>
			<li class="variant_amount">    <input name="variants[stock][]"         type="text"   value="{if $variant['infinity'] || $variant['stock'] == ''}∞{else}{$variant['stock']|escape}{/if}" />{$settings->units}</li>
		</ul>
		{/foreach}		
		</div>
		<ul id=new_variant style='display:none;'>
			<li class="variant_move"><div class="move_zone"></div></li>
			<li class="variant_name"><input name="variants[id][]" type="hidden" value="" /><input name="variants[name][]" type="" value="" /><a class="del_variant" href=""><img src="design/images/cross-circle-frame.png" alt="" /></a></li>
			<li class="variant_sku"><input name="variants[sku][]" type="" value="" /></li>
			<li class="variant_price"><input  name="variants[price][]" type="" value="" /></li>
			<li class="variant_discount"><input name="variants[compare_price][]" type="" value="" /></li>
			<li class="variant_amount"><input name="variants[stock][]" type="" value="∞" />{$settings->units}</li>
			<li class="variant_download">
				<a href='#' class=add_attachment><img src="design/images/cd_add.png" alt="" /></a>
				<div class=browse_attachment style='display:none;'>
					<input type=file name=attachment[]>
					<input type=hidden name=delete_attachment[]>
				</div>
			</li>
		</ul>

		<input class="button_green button_save" type="submit" name="" value="Сохранить" />
		<span class="add" id="add_variant"><i class="dash_link">Добавить вариант</i></span>
	</div>
	<!-- Варианты товара (The End)--> 
	
	<!-- Левая колонка свойств товара -->
	<div class="column_left">
			
		<!-- Параметры страницы -->
		<div class="block layer">
			<h2>Параметры страницы</h2>
			<ul class="list">
				<li><label class=property>Адрес</label>
					<div class="page_url"> /products/</div>
					<input name="url" class="page_url" type="text" value="{$product['url']|escape}" />
				</li>
				<li><label class=property>Заголовок</label>
					<input name="meta_title"  type="text" value="{$product['meta_title']|escape}" />
				</li>
				<li>
					<label class=property>Ключевые слова</label>
					<input name="meta_keywords"  type="text" value="{$product['meta_keywords']|escape}" />
				</li>
				<li>
					<label class=property>Описание</label>
					<textarea name="meta_description"  />{$product['meta_description']|escape}</textarea>
				</li>
			</ul>
		</div>
		<!-- Параметры страницы (The End)-->

		
		{*
		<!-- Экспорт-->
		<div class="block">
			<h2>Экспорт товара</h2>
			<ul>
				<li><input id="exp_yad" type="checkbox" /> <label for="exp_yad">Яндекс Маркет</label> Бид <input  type="" name="" value="12" /> руб.</li>
				<li><input id="exp_goog" type="checkbox" /> <label for="exp_goog">Google Base</label> </li>
			</ul>
		</div>
		<!-- Экспорт (The End)-->
		*}
			
	</div>
	<!-- Левая колонка свойств товара (The End)--> 
	
	<!-- Правая колонка свойств товара -->	
	<div class="column_right">
		
		<!-- Изображения товара -->	
		<div class="block layer images">
			<h2>Изображения товара
			</h2>
			<ul id="imagelist">
			{if $product['images']}
			{foreach $product['images'] as $image}
				<li>
					<a href='#' class="delete"><img src='design/images/cross-circle-frame.png'></a>
					<img src="{$image['filename']|resize:100:100}" alt="" />
					<input type="hidden" name='images[]' value="{$image['id']}">
				</li>{/foreach}
			{/if}
			</ul>

			<div class="block">

			<!-- dropzone для перетаскивания изображений -->	
			{if isset($product['id'])}
				<div id="holder" product_id="{$product['id']}">
					<div class="holder__text">Тяни файл сюда</div>
				</div> 
				<p id="upload" class="hidden"><label>Drag & drop not supported, but you can still upload via this input field:<br><input type="file"></label></p>
				<p id="filereader">File API & FileReader API not supported</p>
				<p id="formdata">XHR2's FormData is not supported</p>
				<p id="progress">XHR2's upload progress isn't supported</p>
				<p>Upload progress: <progress id="uploadprogress" max="100" value="0">0</progress></p>

			{/if}
			<!-- dropzone для перетаскивания изображений (The End) -->

			<span class=upload_image><i class="dash_link" id="upload_image">Добавить изображение</i></span>
			 или 
			 <span class=add_image_url><i class="dash_link" id="add_image_url">загрузить из интернета</i></span>
			<div id="add_image"></div>

			</div>
		</div>

		<div class="block layer">
			<h2>Связанные товары</h2>
			<div id=list class="sortable related_products">
				{if $related_products}
				{foreach $related_products as $related_product}
				<div class="row">
					<div class="move cell">
						<div class="move_zone"></div>
					</div>
					<div class="image cell">
					<input type=hidden name=related_products[] value='{$related_product->id}'>
					<a href="{url id=$related_product->id}">
					<img class=product_icon src='{$related_product->images[0]->filename|resize:35:35}'>
					</a>
					</div>
					<div class="name cell">
					<a href="{url id=$related_product->id}">{$related_product->name}</a>
					</div>
					<div class="icons cell">
					<a href='#' class="delete"></a>
					</div>
					<div class="clear"></div>
				</div>
				{/foreach}
				{/if}
				<div id="new_related_product" class="row" style='display:none;'>
					<div class="move cell">
						<div class="move_zone"></div>
					</div>
					<div class="image cell">
					<input type=hidden name=related_products[] value=''>
					<img class=product_icon src=''>
					</div>
					<div class="name cell">
					<a class="related_product_name" href=""></a>
					</div>
					<div class="icons cell">
					<a href='#' class="delete"></a>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<input type=text name=related id='related_products' class="input_autocomplete" placeholder='Выберите товар чтобы добавить его'>
		</div>

		<input class="button_green button_save" type="submit" name="" value="Сохранить" />
		
	</div>
	<!-- Правая колонка свойств товара (The End)--> 
			<!-- Свойства товара -->
		<div class="block layer">
			<h2>Свойства товара</h2>
				{if isset($product['options'])}
			<ul class="prop_ul">
				{foreach $product['options'] as $fid=>$o}
				<li>
					<label class="property inrow" fid="{$fid}" vid="{$o['vid']}">{$features[$fid]['name']}</label>
					<input class="inrow" type="text" name=options[{$fid}] value="{$o['val']}"/>
				</li>
				{/foreach}
			</ul>
			{/if}
			<!-- Новые свойства -->
			<ul class=new_features>
				<li id=new_feature>
					<label class="property inrow"><input type=text class="inrow" name=new_features_names[]></label>
					<input class="inrow" type="text" name=new_features_values[] />
				</li>
			</ul>
			<span class="add"><i class="dash_link" id="add_new_feature">Добавить новое свойство</i></span>
			<input class="button_green button_save" type="submit" name="" value="Сохранить" />			
		</div>
		
		<!-- Свойства товара (The End)-->
	<!-- Описагние товара -->
	<div class="block layer">
		<h2>Краткое описание</h2>
		<textarea name="annotation" class="editor_small">{$product['annotation']|escape}</textarea>
	</div>
		
	<div class="block">		
		<h2>Полное  описание</h2>
		<textarea name="body" class="editor_large">{$product['body']|escape}</textarea>
	</div>
	<!-- Описание товара (The End)-->
	<input class="button_green button_save" type="submit" name="" value="Сохранить" />
	
</form>
<!-- Основная форма (The End) -->

