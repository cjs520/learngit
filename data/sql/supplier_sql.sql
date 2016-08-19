INSERT INTO `mvm_config` (`cf_name`, `cf_value`, `supplier_id`) VALUES
('mm_text_fonts', 'INFROMAN.TTF', SUPPLIER_ID),
('mm_watermark', '0', SUPPLIER_ID),
('mm_wate_class', '0', SUPPLIER_ID),
('mm_shop_sms', '0', SUPPLIER_ID),
('mm_sms_message1', '您好！您在我们店中订购的商品还为您准备着，请尽快付款，我们将在第一时间为您发货！', SUPPLIER_ID),
('mm_sms_receipt', '1', SUPPLIER_ID),
('mm_sms_delivery', '1', SUPPLIER_ID),
('mm_sms_message4', '您好，您真是一个好买家，祝您购物愉快，欢迎下次再来！', SUPPLIER_ID),
('mm_sms_comment', '1', SUPPLIER_ID),
('mm_sms_message3', '您好！您在我们店中订购的商品已经发货，请注意查收，并祝您购物愉快！', SUPPLIER_ID),
('mm_sms_message2', '您好！您的货款我们已收到，我们将尽快为您发货！', SUPPLIER_ID),
('mm_sms_order', '1', SUPPLIER_ID),
('mm_client_qq2', '58701995', SUPPLIER_ID),
('mm_client_qq1', '61239508', SUPPLIER_ID),
('mm_client_fax', '0596-2968077', SUPPLIER_ID),
('mm_tel', '400-6689-001', SUPPLIER_ID),
('mm_mobile', '400-6689-001', SUPPLIER_ID),
('mm_description', 'MvMmall多用户商城系统,网店系统,名店街,促销团购拍卖信息,做最好的地区企业B2C实体商铺网上销售平台!', SUPPLIER_ID),
('mm_company_num', '闽ICP备10018764号', SUPPLIER_ID),
('mm_mall_address', '福建省漳州市芗城区胜利东路丹霞华庭1901', SUPPLIER_ID),
('mm_close_cess', '1', SUPPLIER_ID),
('mm_close', '0', SUPPLIER_ID),
('mm_skin_name', 'default', SUPPLIER_ID);

INSERT INTO `mvm_badmin_table` (`uid`, `board_name_code`, `board_title`, `register_date`, `od`, `supplier_id`) VALUES
(NULL, 'news', '商铺资讯', 1331023685, 0, SUPPLIER_ID);

INSERT INTO `mvm_bmain` (`uid`, `ps_name`, `author`, `cover`, `board_subject`, `board_hit`, `register_date`, `board_body`, `supplier_id`) VALUES
(NULL, 'news', '销售商铺', '', '商铺开业全面优惠啦', 3, 1331024202, '商铺开业全面优惠啦', SUPPLIER_ID);

INSERT INTO `mvm_nav` (`nid`, `title`, `nav_img`, `style`, `link`, `alt`, `pos`, `target`, `view`, `pid`, `supplier_id`) VALUES
(NULL, '首页', '', '|||', 'index.php', '首页', 'head', 0, 1, 0, SUPPLIER_ID),
(NULL, '商铺动态', '', '|||', 'board.php?action=notice', '商铺动态', 'head', 0, 2, 0, SUPPLIER_ID),
(NULL, '网购常识', '', '|||', 'page.php?action=tips', '网购常识', 'foot', 0, 2, 0, SUPPLIER_ID),
(NULL, '圈子', '', '|||', 'life.php', '圈子', 'head', 0, 3, 0, SUPPLIER_ID),
(NULL, '新品抢先', '', '|||', 'goods.php?action=new', '新品抢先', 'head', 0, 4, 0, SUPPLIER_ID),
(NULL, '折扣专区', '', '|||', 'goods.php?action=sales', '折扣专区', 'head', 0, 5, 0, SUPPLIER_ID),
(NULL, '批发专区', '', '|||', 'goods.php?action=bat', '批发专区', 'head', 0, 6, 0, SUPPLIER_ID),
(NULL, '团购', '', '|||', 'group.php?action=list', '团购', 'head', 0, 7, 0, SUPPLIER_ID),
(NULL, '拍卖', '', '|||', 'auction.php?action=list', '拍卖', 'head', 0, 8, 0, SUPPLIER_ID),
(NULL, '预定', '', '|||', 'preorder.php', '预定', 'head', 0, 9, 0, SUPPLIER_ID);

INSERT INTO `mvm_forumlinks_table` (`id`, `displayorder`, `name`, `url`, `note`, `logo`, `supplier_id`) VALUES
(NULL, 1, '迈维软件', 'http://mvmmall.com', 'MvMmall多用户商城系统,分销系统,网店系统,定制企业级社区商城等电子商务解决方案', '', SUPPLIER_ID);

INSERT INTO `mvm_page_table` (`uid`, `page_name`, `page_key`, `page_desc`, `page_subject`, `page_body`, `register_date`, `supplier_id`) VALUES
(NULL, 'shopdesc', 'mvmmall多用户商城系统 多用户商城 多用户网店 同城购 社区商城', 'mvmmall多用户商城系统 多用户商城 多用户网店 同城购 社区商城', '商铺介绍', '<p>\r\n	商铺介绍\r\n</p>', 1387767651, SUPPLIER_ID);

INSERT INTO `mvm_category` (`uid`, `category_id`, `supplier_id`, `category_name`, `category_key`, `category_desc`, `category_file1`, `category_rank`, `att_list`, `rate`) VALUES
(NULL, 0, SUPPLIER_ID, '默认分类', '', '', '', 0, '', 0.2),
(NULL, 0, SUPPLIER_ID, '默认分类2', '', '', '', 0, '', 0.2);

UPDATE `mvm_member_shop` SET `run_product`='多用户商城,实体商铺,同城购物,企业B2C商城,免费开店,品牌商城' WHERE m_uid='SUPPLIER_ID';