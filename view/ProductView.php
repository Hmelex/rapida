<?PHP

/**
 * 
 * Этот класс использует шаблон product.tpl
 *
 */

require_once('View.php');


class ProductView extends View
{

	function fetch()
	{   
		$product_url = $this->coMaster->uri_arr['path_arr']['url'];
		
		if(empty($product_url)){
			return false;
		}
			
		// Выбираем товар из базы
		$product = $this->products->get_product((string)$product_url);
		if(empty($product) || (!$product['visible'] && empty($_SESSION['admin'])))
			return false;
		
		$product['images'] = $this->products->get_images(array('product_id'=>$product['id']));

		if ( $variants = $this->variants->get_variants(array('product_id'=>$product['id'], 'in_stock'=>true)) ) {
		//~ print "<PRE>";
		//~ print_r($variants);


			
			$product['variants'] = $variants;
			
			// Вариант по умолчанию
			if(($v_id = $this->request->get('variant', 'integer'))>0 && isset($variants[$v_id])) {
				$product['variant'] = $variants[$v_id];
			} else {
				$product['variant'] = reset($variants);
			}
		}
		// Свойства товара
		$features = $this->features->get_features();
		$product['options'] = $this->features->get_product_options($product['id']);
		$this->design->assign('features', $features);


	
		// Автозаполнение имени для формы комментария
		if(!empty($this->user->name)){
			$this->design->assign('comment_name', $this->user->name);
		}else{
			$this->design->assign('comment_name', '');
		}
		
		// заводим в шаблон пустую переменную error, чтобы не вышибало ошибку, когда переменная не задана
		$this->design->assign('error', '');
		
		// Принимаем комментарий
		if ($this->request->method('post') && $this->request->post('comment'))
		{
			$comment = array();
			$comment['name'] = $this->request->post('name');
			$comment['text'] = $this->request->post('text');
			$captcha_code =  $this->request->post('captcha_code', 'string');
			
			
			// Проверяем капчу и заполнение формы
			if ($_SESSION['captcha_code'] != $captcha_code || empty($captcha_code))
			{
				$this->design->assign('error', 'captcha');
			}
			elseif (empty($comment['name']))
			{
				$this->design->assign('error', 'empty_name');
			}
			elseif (empty($comment['text']))
			{
				$this->design->assign('error', 'empty_comment');
			}
			else
			{
				// Создаем комментарий
				$comment['object_id'] = $product['id'];
				$comment['type']      = 'product';
				$comment['ip']        = $_SERVER['REMOTE_ADDR'];
				
				// Если были одобренные комментарии от текущего ip, одобряем сразу
				$this->db->query("SELECT 1 FROM __comments WHERE approved=1 AND ip=? LIMIT 1", $comment['ip']);
				if($this->db->num_rows()>0)
					$comment['approved'] = 1;
				
				// Добавляем комментарий в базу
				$comment_id = $this->comments->add_comment($comment);
				
				// Отправляем email
				$this->notify->email_comment_admin($comment_id);				
				
				// Приберем сохраненную капчу, иначе можно отключить загрузку рисунков и постить старую
				unset($_SESSION['captcha_code']);
				header('location: '.$_SERVER['REQUEST_URI'].'#comment_'.$comment_id);
			}			
		}
				
		// Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
		$this->design->assign('comment_text', isset($comment['text']) ? $comment['text'] : '');
		$this->design->assign('comment_name', isset($comment['name']) ? $comment['name'] : '');
		
		// Связанные товары
		$rp_ids = array();
		if($rp = $this->products->get_related_products($product['id'])){
			$rp_ids = array_keys($rp);
		}
		if(!empty($rp_ids))
		{
			$rp = $this->products->get_products(array('id'=>$rp_ids, 'in_stock'=>1, 'visible'=>1));
			
			$rp_images = $this->products->get_images(array('product_id'=>array_keys($rp)));
			foreach($rp_images as $rp_image){
				if(isset($rp[$rp_image['product_id']])){
					$rp[$rp_image['product_id']]['images'][] = $rp_image;
				}
			}
			$rp_variants = $this->variants->get_variants(array('product_id'=>array_keys($rp), 'in_stock'=>1));
			foreach($rp_variants as $rp_variant)
			{
				if(isset($rp[$rp_variant['product_id']]))
				{
					$rp[$rp_variant['product_id']]['variants'][] = $rp_variant;
				}
			}
			foreach($rp as $id=>$r)
			{
				if(is_array($r))
				{
					$r['variant'] = &$r['variants'][0];
				}
				else
				{
					unset($rp[$id]);
				}
			}
			
		}
		//заводим в шаблон связанные товары
		$this->design->assign('related_products', isset($rp) ? $rp : '');
		
		// Отзывы о товаре
		$comments = $this->comments->get_comments(array('type'=>'product', 'object_id'=>$product['id'], 'approved'=>1, 'ip'=>$_SERVER['REMOTE_ADDR']));
		
		// Соседние товары
		$this->design->assign('next_product', $this->products->get_next_product($product['id']));
		$this->design->assign('prev_product', $this->products->get_prev_product($product['id']));

		// И передаем его в шаблон
		$this->design->assign('product', $product);
		$this->design->assign('comments', $comments);
		
		// Категория и бренд товара
		$product['categories'] = $this->categories->get_categories(array('product_id'=>$product['id']));
		$this->design->assign('brand', $this->brands->get_brand(intval($product['brand_id'])));
		$this->design->assign('category', reset($product['categories']));
		

		// Добавление в историю просмотров товаров
		$max_visited_products = 100; // Максимальное число хранимых товаров в истории
		$expire = time()+60*60*24*30; // Время жизни - 30 дней
		if(!empty($_COOKIE['browsed_products']))
		{
			$browsed_products = explode(',', $_COOKIE['browsed_products']);
			// Удалим текущий товар, если он был
			if(($exists = array_search($product['id'], $browsed_products)) !== false)
				unset($browsed_products[$exists]);
		}
		// Добавим текущий товар
		$browsed_products[] = $product['id'];
		$cookie_val = implode(',', array_slice($browsed_products, -$max_visited_products, $max_visited_products));
		setcookie("browsed_products", $cookie_val, $expire, "/");
		
		$this->design->assign('meta_title', $product['meta_title']);
		$this->design->assign('meta_keywords', $product['meta_keywords']);
		$this->design->assign('meta_description', $product['meta_description']);
		
		return $this->design->fetch('product.tpl');
	}
	


}
