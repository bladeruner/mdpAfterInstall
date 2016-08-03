<?php
/**
 * Resolver to set system settings
 * 
 * @package mdpafterinstall
 * @subpackage build
 */
$success= true;
$tmp = array(
	'cultureKey' => 'ru'
	,'fe_editor_lang' => 'ru'
	,'publish_default' => 1
	,'upload_maxsize' => '10485760'	
	, 'topmenu_show_descriptions' => 0
	, 'locale' => 'ru_RU.utf-8'
	, 'manager_lang_attribute' => 'ru'
	, 'manager_language' => 'ru'
	, 'automatic_alias' => 1
	, 'friendly_urls' => 1
	, 'global_duplicate_uri_check' => 0
	, 'link_tag_scheme' => 'abs'
	, 'container_suffix' => ''
	, 'friendly_urls_strict' => 1
	, 'use_alias_path' => 1
	, 'request_method_strict' => 1
	, 'tiny.base_url' => '/'
	, 'tiny.path_options' => 'rootrelative'
	, 'friendly_alias_translit' => 'russian'
);
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
		
			$sitemap = $object->xpdo->getObject('modResource',array('alias' => 'sitemap'));
			if (!$sitemap) {
				$sitemap = $object->xpdo->newObject('modResource');
				$sitemap->fromArray(array(
					'pagetitle' => 'sitemap.xml',
					'template' => 0,
					'published' => 1,
					'hidemenu' => 1,
					'alias' => 'sitemap' 
					,'content_type' => 2, 
					'richtext' => 0, 
					'menuindex' => 198, 
					'content' =>'[[!pdoSitemap? &checkPermissions=`list`]]'
				));
				$sitemap->save();
			}
			
			$robots = $object->xpdo->getObject('modResource',array('alias' => 'robots'));
			if (!$robots) {
				$robots = $object->xpdo->newObject('modResource');
				$robots->fromArray(array(
					'pagetitle' => 'robots.txt',
					'template' => 0,
					'published' => 1,
					'hidemenu' => 1,
					'alias' => 'robots',
					'content_type' => 3,
					'richtext' => 0, 
					'menuindex' => 199, 
					'content' => 'User-agent: * Disallow: /manager/ Disallow: /assets/components/ Allow: /assets/uploads/ Disallow: /core/ Disallow: /connectors/ Disallow: /index.php Disallow: /search Disallow: /profile/ Disallow: *? Host: [[++site_url]] Sitemap: [[++site_url]]sitemap.xml' 
				));
				$robots->save();
			}
		
			foreach ($tmp as $k => $v) {
				$setting = $object->xpdo->getObject('modSystemSetting',array('key' => $k));
				if ($setting) {
					$object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Attempting to set "'.$k.'" setting to "'.$v.'".');
					$setting->set('value',$v);
					$setting->save();
				}
				else {
					$setting = $object->xpdo->newObject('modSystemSetting');
					$setting->set('key',$k);
					$setting->set('value',$v);
					$setting->save();
				}
				unset($setting);
			}
			
			$mainTemplate = $object->xpdo->getObject('modTemplate',array('id' => '1'));
			if ($mainTemplate) {
				$mainTemplate->set('templatename','Главная страница сайта');
				$mainTemplate->set('content','<html>
<head>
<title>[[*longtitle:default=`[[*pagetitle]]`]]</title>
[[Canonical]]
[[*seo_keywords:notempty=`<meta name="keywords" content="[[*seo_keywords]]">`]]
[[*seo_description:notempty=`<meta name="description" content="[[*seo_description]]">`]]
[[*seo_index:ne=`1`:then=`<meta name="robots" content="noindex,nofollow">`]]
</head>
<body>
[[*content]]
</body>
</html>');
				$categ = $object->xpdo->getObject('modCategory',array('category' => 'SEO'));				
				$templateVars = $categ->getMany('modTemplateVar');
				foreach ($templateVars as $templateVar) {
					$tvt = $object->xpdo->getObject('modTemplateVarTemplate',array('templateid' => '1','tmplvarid' => $templateVar->get('id')));
					if (!$tvt) {
						$tvt = $object->xpdo->newObject('modTemplateVarTemplate');
						$tvt->set('templateid','1');
						$tvt->set('tmplvarid',$templateVar->get('id'));
						$tvt->save();
					}
				}				
				$mainTemplate->save();
			}
			
			$contType = $object->xpdo->getObject('modContentType',array('name' => 'HTML'));
			if ($contType) {
				$contType->set('file_extensions','');
				$contType->save();
			}
			
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $success= true;
            break;
    }	
	
unset($tmp);	

return $success;