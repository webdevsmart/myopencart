<?php
class ControllerSellerCatalogSeller extends ControllerSellerCatalog {
	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->language->load('product/category');
		$this->load->model('localisation/country');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
	}
	
	public function index() {
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		
		if (isset($this->request->get['sort'])) {
			$order_by = $this->request->get['sort'];
		} else {
			$order_by = 'ms.nickname';
		}
		
		if (isset($this->request->get['order'])) {
			$order_way = $this->request->get['order'];
		} else {
			$order_way = 'ASC';
		}
		
		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
		
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_product_limit') ? $this->config->get('config_product_limit') : $this->config->get($this->config->get('config_theme') . '_product_limit');
		}
		
		$this->data['products'] = array();

		$results = $this->MsLoader->MsSeller->getSellers(
			array(
				'seller_status' => array(MsSeller::STATUS_ACTIVE)
			),
			array(
				'order_by'	=> $order_by,
				'order_way'	=> $order_way,
				'offset'	=> ($page - 1) * $limit,
				'limit'		=> $limit
			)
		);

		$total_sellers = isset($results[0]['total_rows']) ? $results[0]['total_rows'] : 0;

		foreach ($results as $result) {
			$avatar = $this->MsLoader->MsFile->resizeImage($result['ms.avatar'] && file_exists(DIR_IMAGE . $result['ms.avatar']) ? $result['ms.avatar'] : 'ms_no_image.jpg', $this->config->get('msconf_seller_avatar_seller_list_image_width'), $this->config->get('msconf_seller_avatar_seller_list_image_height'));

			$banner = '';
			if ($this->config->get('msconf_enable_seller_banner')) {
				if ($result['banner'] && file_exists(DIR_IMAGE . $result['banner'])) {
					$banner = $this->MsLoader->MsFile->resizeImage($result['banner'], $this->config->get('msconf_product_seller_banner_width'), $this->config->get('msconf_product_seller_banner_height'), 'w');
				}
			}

			$seller_settings = $this->MsLoader->MsSetting->getSellerSettings(array('seller_id' => $result['seller_id']));
			$defaults = $this->MsLoader->MsSetting->getSellerDefaults();
			$settings = array_merge($defaults, $seller_settings);

			$country = $this->model_localisation_country->getCountry($settings['slr_country']);
			$settings['slr_country'] = (isset($country['name']) ? $country['name'] : '');

			$products = $this->MsLoader->MsProduct->getProducts(
				array(
					'seller_id' => $result['seller_id'],
					'language_id' => $this->config->get('config_language_id'),
					'product_status' => array(MsProduct::STATUS_ACTIVE),
					'oc_status' => 1
				),
				array(
					'order_by'	=> 'pd.name',
					'order_way'	=> 'ASC',
					'offset'	=> 0,
					'limit'		=> 3
				)
			);

			$total_products = isset($products[0]['total_rows']) ? $products[0]['total_rows'] : 0;

			foreach($products as $key=>$product) {
				$image = $this->MsLoader->MsFile->resizeImage($product['p.image'] && file_exists(DIR_IMAGE . $product['p.image']) ? $product['p.image'] : 'no_image.png', 100, 100);
				$product['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
				$product['p.image'] = $image;

				$products[$key] = $product;
			}

			$this->data['sellers'][] = array(
				'seller_id' => $result['seller_id'],
				'thumb' => $avatar,
				'banner' => $banner,
				'nickname' => $result['ms.nickname'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['ms.description'], ENT_QUOTES, 'UTF-8')), 0, 200) . '..',
				'href' => $this->url->link('seller/catalog-seller/profile', '&seller_id=' . $result['seller_id']),
				'products_href' => $this->url->link('seller/catalog-seller/products', 'seller_id=' . $result['seller_id']),
				'settings' => $settings,
				'products' => $products,
				'total_products' => $total_products
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
						
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_asc'),
			'value' => 'ms.nickname-ASC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.nickname&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_desc'),
			'value' => 'ms.nickname-DESC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.nickname&order=DESC' . $url)
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		
		$this->data['limits'] = array();
		
		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=25')
		);
		
		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=50')
		);

		$this->data['limits'][] = array(
			'text'  => 75,
			'value' => 75,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=75')
		);
		
		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=100')
		);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		
		$pagination = new Pagination();
		$pagination->total = $total_sellers;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('seller/catalog-seller', $url . '&page={page}');
	
		$this->data['pagination'] = $pagination->render();
		$this->data['results'] = sprintf($this->language->get('text_pagination'), ($total_sellers) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($total_sellers - 5)) ? $total_sellers : ((($page - 1) * 5) + 5), $total_sellers, ceil($total_sellers / 5));

		// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
		if ($page == 1) {
			$this->document->addLink($this->url->link('seller/catalog-seller', $url, true), 'canonical');
		} elseif ($page == 2) {
			$this->document->addLink($this->url->link('seller/catalog-seller', $url, true), 'prev');
		} else {
			$this->document->addLink($this->url->link('seller/catalog-seller', $url . '&page='. ($page - 1), true), 'prev');
		}

		if($pagination->limit && ceil($pagination->total / $pagination->limit) > $pagination->page) {
			$this->document->addLink($this->url->link('seller/catalog-seller', $url . '&page='. ($pagination->page + 1)), 'next');
		}

		$this->data['sort'] = $order_by;
		$this->data['order'] = $order_way;
		$this->data['limit'] = $limit;		
		
		$this->data['continue'] = $this->url->link('common/home');

		$this->document->setTitle($this->language->get('ms_catalog_sellers_heading'));
		$this->document->setDescription($this->language->get('ms_catalog_sellers_description'));
		$this->data['h1'] = $this->language->get('ms_catalog_sellers_heading');
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'), 
			)
		));
		
		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}
		
	public function profile() {
		if (isset($this->request->get['seller_id'])) {
			$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);
		}

		if (!isset($seller) || empty($seller) || $seller['ms.seller_status'] != MsSeller::STATUS_ACTIVE) {
			$this->response->redirect($this->url->link('seller/catalog-seller', '', 'SSL'));
			return;
		}

		$seller_id = $this->request->get['seller_id'];
		$settings = $this->MsLoader->MsSetting->getSellerSettings(array("seller_id" => $seller_id));
		$default_settings = $this->MsLoader->MsSetting->getSellerDefaults();
		$settings = array_merge($default_settings, $settings);

		$country = $this->model_localisation_country->getCountry($settings['slr_country']);
		$settings['slr_country'] = (isset($country['name']) ? $country['name'] : '');

		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
		$this->document->addScript('catalog/view/javascript/dialog-sellercontact.js');

		if($this->config->get('msconf_reviews_enable')) {
			$this->MsLoader->MsHelper->addStyle('pagination');
			$this->document->addScript('catalog/view/javascript/pagination.min.js');
			$this->document->addScript('catalog/view/javascript/ms-review.js');

			$this->data['reviews'] = $rating_stats = array();
			$this->data['total_reviews'] = $this->data['rating_stats'] = $this->data['avg_rating'] = 0;

			for($i = 1; $i <= 5; $i++) {
				$rating_stats[$i] = array('votes' => 0, 'percentage' => 0);
			}

			$sum_rating = 0;

			$reviews = $this->MsLoader->MsReview->getReviews(array('seller_id' => $seller_id));
			$total_reviews = isset($reviews[0]) ? $reviews[0]['total_rows'] : 0;

			$this->load->model('account/customer');
			$this->load->model('catalog/product');

			foreach ($reviews as &$review) {
				$sum_rating += $review['rating'];

				$rating_stats[$review['rating']]['votes'] += 1;

				$review['author'] = $this->model_account_customer->getCustomer($review['author_id']);
				$review['date_created'] = date($this->language->get('date_format_short'), strtotime($review['date_created']));

				foreach ($review['attachments'] as &$attachment) {
					$attachment['fullsize'] = $this->model_tool_image->resize($attachment['attachment'], $this->config->get($this->config->get('config_theme') . '_image_popup_width'), $this->config->get($this->config->get('config_theme') . '_image_popup_height'));
					$attachment['thumb'] = $this->model_tool_image->resize($attachment['attachment'], 70, 70);
				}

				$reviewed_product = $this->model_catalog_product->getProduct($review['product_id']);
				$review['product'] = array(
					'name' => $reviewed_product['name'],
					'href' => $this->url->link('product/product', 'product_id=' . $reviewed_product['product_id']),
					'model' => $reviewed_product['model'],
					'price' => $this->currency->format($reviewed_product['price'])
				);

				$comments = $this->MsLoader->MsReview->getReviewComments($review['review_id']);
				$review['total_comments'] = isset($comments[0]) ? $comments[0]['total_rows'] : 0;
			}

			foreach ($rating_stats as &$rating_stat) {
				$rating_stat['percentage'] = $total_reviews > 0 ? round($rating_stat['votes'] / $total_reviews * 100, 1) : 0;
			}
			krsort($rating_stats);

			$this->data['reviews'] = $reviews;
			$this->data['total_reviews'] = $total_reviews;
			$this->data['rating_stats'] = $rating_stats;
			$this->data['avg_rating'] = $total_reviews > 0 ? round($sum_rating / $total_reviews, 1) : 0;
			$this->data['feedback_history'] = $this->MsLoader->MsReview->getFeedbackHistory(array('seller_id' => $seller_id));
		}

		if ($seller['ms.avatar'] && file_exists(DIR_IMAGE . $seller['ms.avatar'])) {
			$this->data['seller']['thumb'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		} else {
			$this->data['seller']['thumb'] = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		}

		if ($this->config->get('msconf_enable_seller_banner')) {
			if ($seller['banner'] && file_exists(DIR_IMAGE . $seller['banner'])) {
				$this->data['seller']['banner'] = $this->MsLoader->MsFile->resizeImage($seller['banner'], $this->config->get('msconf_product_seller_banner_width'), $this->config->get('msconf_product_seller_banner_height'), 'w');
			}
		}
		
		$this->data['seller']['created'] = date($this->language->get('date_format_short'), strtotime($seller['ms.date_created']));
		$this->data['seller']['settings'] = $settings;
		$this->data['seller']['nickname'] = $seller['ms.nickname'];
		$this->data['seller']['seller_id'] = $seller['seller_id'];
		$this->data['seller']['description'] = html_entity_decode($seller['ms.description'], ENT_QUOTES, 'UTF-8');
		$this->data['seller']['href'] = $this->url->link('seller/catalog-seller/products', 'seller_id=' . $seller['seller_id']);

		// social links
		if ($this->config->get('msconf_sl_status')) {
			$this->MsLoader->MsHelper->addStyle('multimerch_social_links');
			$this->data['seller']['social_links'] = $this->MsLoader->MsSocialLink->getSellerChannels($seller['seller_id']);
			foreach ($this->data['seller']['social_links'] as &$link) {
				if($this->MsLoader->MsHelper->isValidUrl($link['channel_value'])) {
					$link['image'] = $this->model_tool_image->resize($link['image'], $this->config->get('msconf_sl_icon_width'), $this->config->get('msconf_sl_icon_height'));
				} else {
					unset($link);
				}
			}
		}

		// badges
		$badges = array_unique(array_merge(
			$this->MsLoader->MsBadge->getBadges(array('seller_id' => $seller['seller_id'])),
			$this->MsLoader->MsBadge->getBadges(array('seller_group_id' => $seller['ms.seller_group'])),
			$this->MsLoader->MsBadge->getBadges(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')))
		), SORT_REGULAR);

		foreach ($badges as &$badge) {
			$badge['image'] = $this->model_tool_image->resize($badge['image'], $this->config->get('msconf_badge_width'), $this->config->get('msconf_badge_height'));
		}
		$this->data['seller']['badges'] = $badges;

		// load disqus data
		$this->data = array_merge($this->data, $this->load->language('module/multimerch_disqus'));
		$this->data['disqus_identifier'] = 'sid' . $this->request->get['seller_id'];
		$this->data['disqus_url'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $this->request->get['seller_id'], 'SSL');

		$orders = $this->MsLoader->MsSuborder->getSuborders(array(
			'seller_id' => $seller['seller_id']
		));
		$total_sales = isset($orders[0]['total_rows']) ? $orders[0]['total_rows'] : 0;
		$this->data['seller']['total_sales'] = $total_sales;
		$this->data['seller']['total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		));
				
		$products = $this->MsLoader->MsProduct->getProducts(
			array(
				'seller_id' => $seller['seller_id'],
				'language_id' => $this->config->get('config_language_id'),
				'product_status' => array(MsProduct::STATUS_ACTIVE),
				'oc_status' => 1
			),
			array(
				'order_by'	=> 'pd.name',
				'order_way'	=> 'ASC',
				'offset'	=> 0,
				'limit'		=> 6
			)
		);

		if (!empty($products)) {
			foreach ($products as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				if ($product_info['image'] && file_exists(DIR_IMAGE . $product_info['image'])) {
					$image = $this->MsLoader->MsFile->resizeImage($product_info['image'],$this->config->get($this->config->get('config_theme') . '_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->MsLoader->MsFile->resizeImage('no_image.png', $this->config->get($this->config->get('config_theme') . '_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height'));
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $product_info['rating'];
				} else {
					$rating = false;
				}
							
				$this->data['seller']['products'][] = array(
					'product_id' => $product['product_id'],				
					'thumb' => $image,
					'description' => $product_info['description'],
					'tax' => $tax,
					'name' => $product_info['name'],
					'price' => $price,
					'special' => $special,
					'rating' => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
				);				
			}
		} else {
			$this->data['seller']['products'] = array();
		}

		$this->data['seller_id'] = $this->request->get['seller_id'];

		$this->data['ms_catalog_seller_profile_view'] = sprintf($this->language->get('ms_catalog_seller_profile_view'), $this->data['seller']['nickname']);
		$this->data['contactForm'] = $this->MsLoader->MsHelper->renderPmDialog($this->data);

		$tags = array(
			'[seller_nickname]' => $this->data['seller']['nickname'],
			'[store_name]' => $this->config->get('config_name')
		);

		$this->document->setTitle($this->data['seller']['nickname']);
		$this->data['h1'] = $this->data['seller']['nickname'];

		if (isset($this->data['seller']['description']) AND $this->data['seller']['description']){
			$description_for_meta = $this->MsLoader->MsHelper->generateMetaDescription($this->data['seller']['description']);
			$this->document->setDescription($description_for_meta);
		}
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'), 
			),
			array(
				'text' => sprintf($this->language->get('ms_catalog_seller_profile_breadcrumbs'), $this->data['seller']['nickname']),
				'href' => $this->url->link('seller/catalog-seller/profile', '&seller_id='.$seller['seller_id'], 'SSL'),
			)
		));

		$this->document->addLink($this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']), 'canonical');

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller-profile');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}

	public function products() {
		
		if (isset($this->request->get['seller_id'])) {
			$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);
		}
		if (!isset($seller) || empty($seller) || $seller['ms.seller_status'] != MsSeller::STATUS_ACTIVE) {
			$this->response->redirect($this->url->link('seller/catalog-seller', '', 'SSL'));
			return;
		}

		$seller_id = $this->request->get['seller_id'];
		$settings = $this->MsLoader->MsSetting->getSellerSettings(array("seller_id" => $seller_id));
		$default_settings = $this->MsLoader->MsSetting->getSellerDefaults();
		$settings = array_merge($default_settings, $settings);
		$country = $this->model_localisation_country->getCountry($settings['slr_country']);
		$settings['slr_country'] = (isset($country['name']) ? $country['name'] : '');

		$ms_categories = $this->MsLoader->MsSeller->getSellerMsCategories($this->request->get['seller_id']);
		$childs = array();

		foreach($ms_categories as &$ms_category) {
			$ms_category['href'] = $this->url->link('seller/catalog-seller/products', 'seller_id=' . $this->request->get['seller_id'] . '&ms_category_id=' . $ms_category['category_id']);
			$childs[$ms_category['parent_id']][] = &$ms_category;
		}

		foreach($ms_categories as $key => &$ms_category) {
			if (isset($childs[$ms_category['category_id']])) {
				$ms_category['childs'] = $childs[$ms_category['category_id']];
			}

			if($ms_category['parent_id'] != 0) unset($ms_categories[$key]);
		}

		// @todo Change tree display in the view
		$this->data['seller']['ms_categories'] = $ms_categories;

		$this->data['seller']['seller_id'] = $seller_id;
		$this->data['seller']['href'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']);

		/* seller info part */
		if ($seller['ms.avatar'] && file_exists(DIR_IMAGE . $seller['ms.avatar'])) {
			$image = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		} else {
			$image = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		}
		$this->data['seller']['settings'] = $settings;
		$this->data['seller']['nickname'] = $seller['ms.nickname'];
		$this->data['seller']['description'] = html_entity_decode($seller['ms.description'], ENT_QUOTES, 'UTF-8');
		$this->data['seller']['thumb'] = $image;
		$this->data['seller']['href'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']);

		$orders = $this->MsLoader->MsSuborder->getSuborders(array(
			'seller_id' => $seller['seller_id']
		));
		$total_sales = isset($orders[0]['total_rows']) ? $orders[0]['total_rows'] : 0;
		$this->data['seller']['total_sales'] = $total_sales;
		$this->data['seller']['total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		));

		/* seller products part */
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		
		$available_sorts = array('pd.name-ASC', 'pd.name-DESC', 'pd.name');
		if (isset($this->request->get['sort'])) {
			$order_by = $this->request->get['sort'];
			if (!in_array($order_by, $available_sorts)) {
				$order_by = 'pd.name';
			}
		} else {
			$order_by = 'pd.name';
		}
		
		if (isset($this->request->get['order'])) {
			$order_way = $this->request->get['order'];
		} else {
			$order_way = 'ASC';
		}
		
		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['ms_category_id'])) {
			$url .= '&ms_category_id=' . $this->request->get['ms_category_id'];
		}

		$this->data['limits'] = array();

		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=25&seller_id=' . $seller['seller_id'])
		);

		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=50&seller_id=' . $seller['seller_id'])
		);

		$this->data['limits'][] = array(
			'text'  => 75,
			'value' => 75,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=75&seller_id=' . $seller['seller_id'])
		);

		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=100&seller_id=' . $seller['seller_id'])
		);
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
//			$limit = $this->config->get('config_product_limit') ? $this->config->get('config_product_limit') : $this->config->get($this->config->get('config_theme') . '_product_limit');
			$limit = $this->data['limits'][0]['value'];
		}

		$this->data['products'] = array();


		$sort = array(
			//'filter_category_id' => $category_id, 
			'order_by'               => $order_by,
			'order_way'              => $order_way,
			'offset'              => ($page - 1) * $limit,
			'limit'              => $limit,
		);

		$total_sort = array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		);
		
		if(isset($this->request->get['search'])){
			$search = $this->request->get['search'];
			$sort['filters'] = array
			(
				'pd.name' 			=> $search
			);
			$total_sort['search'] = $search;
			
		}
		
		
		$products = $this->MsLoader->MsProduct->getProducts(
			array(
				'seller_id' => $seller['seller_id'],
				'language_id' => $this->config->get('config_language_id'),
				'category_id' => (isset($this->request->get['category_id'])) ? $this->request->get['category_id'] : NULL,
				'ms_category_id' => (isset($this->request->get['ms_category_id'])) ? $this->request->get['ms_category_id'] : NULL,
				'product_status' => array(MsProduct::STATUS_ACTIVE),
				'oc_status' => 1
			),
			$sort
		);

		$total_products = !empty($products) ? $products[0]['total_rows'] : 0;

		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		if (!empty($products)) {
			foreach ($products as $product) {
				$product_data = $this->model_catalog_product->getProduct($product['product_id']);
				if ($product_data['image'] && file_exists(DIR_IMAGE . $product_data['image'])) {
					$image = $this->MsLoader->MsFile->resizeImage($product_data['image'], $this->config->get($this->config->get('config_theme') . '_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->MsLoader->MsFile->resizeImage('no_image.png', $this->config->get($this->config->get('config_theme') . '_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height'));
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
						
				if ((float)$product_data['special']) {
					$special = $this->currency->format($this->tax->calculate($product_data['special'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = $product_data['rating'];
				} else {
					$rating = false;
				}
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product_data['special'] ? $product_data['special'] : $product_data['price']);
				} else {
					$tax = false;
				}
							
				$this->data['seller']['products'][] = array(
					'product_id' => $product['product_id'],
					'thumb' => $image,
					'name' => $product_data['name'],
					'price' => $price,
					'tax' => $tax,
					'description' => utf8_substr(strip_tags(html_entity_decode($product['pd.description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length') ? $this->config->get('config_product_description_length') : $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
					'special' => $special,
					'rating' => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product_data['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $product_data['product_id']),						
				);
			}
		} else {
			$this->data['seller']['products'] = array();
		}
		
		
		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		if (isset($this->request->get['ms_category_id'])) {
			$url .= '&ms_category_id=' . $this->request->get['ms_category_id'];
		}
						
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=pd.name&order=ASC&seller_id=' . $seller['seller_id'] . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=pd.name&order=DESC&seller_id=' . $seller['seller_id'] . $url)
		);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['ms_category_id'])) {
			$url .= '&ms_category_id=' . $this->request->get['ms_category_id'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_products;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('seller/catalog-seller/products', 'seller_id=' . $seller['seller_id'] .  $url . '&page={page}');

		$this->data['pagination'] = $pagination->render();

		$this->document->addLink($this->url->link('seller/catalog-seller/products', 'seller_id=' . $this->request->get['seller_id']), 'canonical');

		if ($pagination->limit && ceil($pagination->total / $pagination->limit) > $pagination->page) {
			$this->document->addLink($this->url->link('seller/catalog-seller/products', 'seller_id=' . $this->request->get['seller_id'] . $url . '&page=' . ($pagination->page + 1)), 'next');
		}

		if ($pagination->page > 1) {
			$this->document->addLink($this->url->link('seller/catalog-seller/products', 'seller_id=' . $this->request->get['seller_id'] . $url . '&page=' . ($pagination->page - 1)), 'prev');
		}

		$this->data['results'] = sprintf($this->language->get('text_pagination'), ($total_products) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total_products - $limit)) ? $total_products : ((($page - 1) * $limit) + $limit), $total_products, ceil($total_products / $limit));

		$this->data['sort'] = $order_by;
		$this->data['order'] = $order_way;
		$this->data['limit'] = $limit;		
		
		$this->data['ms_catalog_seller_products'] = sprintf($this->language->get('ms_catalog_seller_products_heading'), $seller['ms.nickname']);

		$breadcrumbs = array(
			array(
				'text' => $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'),
			),
			array(
				'text' => sprintf($this->language->get('ms_catalog_seller_profile_breadcrumbs'), $this->data['seller']['nickname']),
				'href' => $this->url->link('seller/catalog-seller/profile', '&seller_id='.$seller['seller_id'], 'SSL'),
			),
			array(
				'text' => sprintf($this->language->get('ms_catalog_seller_products_breadcrumbs'), $seller['ms.nickname']),
				'href' => $this->url->link('seller/catalog-seller/products', '&seller_id='.$seller['seller_id'], 'SSL'),
			)
		);

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs($breadcrumbs);

		if (isset($this->request->get['ms_category_id'])){
			$current_ms_category = $this->MsLoader->MsCategory->getCategories(
				array(
					'category_id' => $this->request->get['ms_category_id'],
					'single' => true,
					'ms_path' => true
				)
			);
			$ms_path = explode(',',$current_ms_category['ms_path']);
			//todo query in foreach
			foreach ($ms_path as $category_id){
				$ms_cat = $this->MsLoader->MsCategory->getCategories(
					array(
						'category_id' => $category_id,
						'single' => true
					)
				);
				if (isset($ms_cat['name']) AND $ms_cat['name']) {
					$breadcrumbs[] = array(
						'text' => $ms_cat['name'],
						'href' => $this->url->link('seller/catalog-seller/products', 'seller_id=' . $this->request->get['seller_id'] . '&ms_category_id=' . $category_id)
					);
				}
			}
			$this->data['h1'] = $current_ms_category['name'];
			if (isset($current_ms_category['meta_title']) AND $current_ms_category['meta_title']){
				$this->document->setTitle($current_ms_category['meta_title']);
			}else{
				$this->document->setTitle($current_ms_category['name']);
			}
			if (isset($current_ms_category['meta_description']) AND $current_ms_category['meta_description']){
				$this->document->setDescription($current_ms_category['meta_description']);
			}
			if (isset($current_ms_category['meta_keyword']) AND $current_ms_category['meta_keyword']){
				$this->document->setKeywords($current_ms_category['meta_keyword']);
			}

		}else{
			$this->document->setTitle(sprintf($this->language->get('ms_catalog_seller_products_heading'), $seller['ms.nickname']));
			$this->data['h1'] = sprintf($this->language->get('ms_catalog_seller_products_heading'), $seller['ms.nickname']);
			if (isset($this->data['seller']['description']) AND $this->data['seller']['description']){
				$description_for_meta = $this->MsLoader->MsHelper->generateMetaDescription($this->data['seller']['description']);
				$this->document->setDescription($description_for_meta);
			}
		}

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs($breadcrumbs);
		
		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller-products');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}

	public function jxSubmitContactDialog() {
		if (!$this->customer->getId() || ($this->customer->getId() == $this->request->post['seller_id'])) return;
		$serviceLocator = $this->MsLoader->load('\MultiMerch\Module\MultiMerch')->getServiceLocator();
		$mailTransport = $serviceLocator->get('MailTransport');
		$mails = new \MultiMerch\Mail\Message\MessageCollection();

		$seller_id = $this->request->post['seller_id'];
		$product_id = $this->request->post['product_id'];
		$seller_email = $this->MsLoader->MsSeller->getSellerEmail($seller_id);
		$seller_name = $this->MsLoader->MsSeller->getSellerName($seller_id);
		$message_text = trim($this->request->post['ms-sellercontact-text']);
		$customer_name = $this->customer->getFirstname() . ' ' . $this->customer->getLastname();
		$customer_email = $this->customer->getEmail();

		if ($product_id) {
			$product = $this->MsLoader->MsProduct->getProduct($product_id);
			$product_name = $product['languages'][$this->MsLoader->MsHelper->getLanguageId($this->config->get('config_language'))]['name'];
		}

		$title = $product_id ? sprintf($this->language->get('ms_conversation_title_product'), $product_name) : sprintf($this->language->get('ms_conversation_title'), $customer_name);

		$json = array();
		if (empty($message_text)) {
			$json['errors'][] = $this->language->get('ms_error_contact_allfields');
		}

		if (utf8_strlen($message_text) > 2000) {
			$json['errors'][] = $this->language->get('ms_error_contact_text');
		}

		if (!isset($json['errors'])) {
			if ($this->config->get('mmess_conf_enable') == 1) {
				$conversation_id = $this->MsLoader->MsConversation->createConversation(
					array(
						'product_id' => $product_id,
						'title' => $title,
						'conversation_from' => $this->customer->getId(),
					)
				);
				$participants = array($this->customer->getId(),$seller_id);
				$this->MsLoader->MsConversation->addConversationParticipants($conversation_id,$participants);
				$this->MsLoader->MsMessage->createMessage(
					array(
						'conversation_id' => $conversation_id,
						'from' => $this->customer->getId(),
						'message' => $message_text
					)
				);

				$MailSellerPrivateMessage = $serviceLocator->get('MailSellerPrivateMessage', false)
					->setTo($seller_email)
					->setData(array(
						'customer_name' => $customer_name,
						'customer_message' => $message_text,
						'title' => $title,
						'product_id' => $product_id,
						'addressee' => $seller_name
					));
				$mails->add($MailSellerPrivateMessage);
				$mailTransport->sendMails($mails);

				$json['success'] = $this->language->get('ms_sellercontact_success');
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}
?>