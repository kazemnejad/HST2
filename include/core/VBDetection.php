<?php
require_once 'CMSDetection.php';

class vBulettinDetector extends CMSDetector{
	
	protected function init(){
		$this->dirArray = array('adminCP', 'archive', 'clientscript', 'cpstyle', 'customavatars', 'customgroupicons', 'customprofilepics', 'images', 'includes', 'modcp', 'packages', 'vb', 'store_sitemap');
		$this->mainUrlArray = array('ajax.php','album.php','announcement.php','apichain.php','api.php','assetmanage.php','asset.php','attachment_inlinemod.php','attachment.php','blog_ajax.php','blog_attachment.php','blog_callback.php','blog_external.php','blog_inlinemod.php','blog.php','blog_post.php','blog_report.php','blog_subscription.php','blog_tag.php','blog_usercp.php','calendar.php','ckeditor.php','content.php','converse.php','cron.php','css.php','editor.php','editpost.php','entry.php','external.php','faq.php','forumdisplay.php','forum.php','global.php','group_inlinemod.php','group.php','groupsubscription.php','image.php','index.php','infraction.php','inlinemod.php','joinrequests.php','list.php','login.php','member_inlinemod.php','memberlist.php','member.php','misc.php','mobile.php','moderation.php','moderator.php','newattachment.php','newreply.php','newthread.php','online.php','payment_gateway.php','payments.php','picturecomment.php','picture_inlinemod.php','picture.php','poll.php','posthistory.php','postings.php','printthread.php','private.php','profile.php','register.php','report.php','reputation.php','search.php','sendmessage.php','showgroups.php','showpost.php','showthread.php','subscription.php','tags.php','threadrate.php','threadtag.php','usercp.php','usernote.php','validator.php','visitormessage.php','widget.php','xmlsitemap.php');
	}
	
	protected function detect(){

		$percent = array();
 		
		$url = parse_url($this->baseURL);
		$percent['meta'] = array($this->checkMeta(array('generator' => 'vbulletin', 'vb_meta_bburl' => $url['host'],)), 5);
		self::LOG('META: '.$percent['meta'][0], "VB Detect");
		
		$percent['dir'] = array($this->checkDir(), 3);
		self::LOG('DIR: '.$percent['dir'][0], "VB Detect");
		
		$percent['url'] = array($this->checkURL(), 4);
		self::LOG('URL: '.$percent['url'][0], "VB Detect");
		
		$sum = 0;
		$weights = 0;
		foreach ($percent as $value) {
			$sum += $value[0]* $value[1];
			$weights += $value[1];
		}
		return $sum/$weights;
	}
}