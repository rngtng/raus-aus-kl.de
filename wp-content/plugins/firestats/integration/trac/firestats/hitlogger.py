import subprocess
from trac.core import *
from trac.web.api import *

class FireStatsPlugin(Component):
	implements(IRequestFilter)

	def pre_process_request(self, req, handler):
	
		if not req.path_info.startswith('/chrome'):
			firestats_location = self.env.config.get('firestats', 'firestats_directory')
			if not firestats_location:
				self.log.warning('FireStats: missing parameter: firestats_directory, aborting')
				return handler

			firestats_site_id = self.env.config.get('firestats', 'firestats_site_id')
			if not firestats_site_id:
				firestats_site_id = '0';

			ip = req.remote_addr	
			referer = req.get_header('referer')
			useragent = req.get_header('user-agent')

			x_forwarded = req.get_header('X-Forwarded-For')
			if not x_forwarded:
				x_forwarded = "";

			url = req.abs_href(req.path_info)
			if not ip:
				ip = ''
			if not referer:
				referer = ''
			if not useragent:
				useragent = ''
			if not url:
				url = ''
			cmd = '$_SERVER["REMOTE_ADDR"]="'+ip+'";$_SERVER["HTTP_USER_AGENT"]="'+useragent+'";$_SERVER["REQUEST_URI"]="'+url+'";$_SERVER["HTTP_REFERER"]="'+referer+'";$GLOBALS["FS_SITE_ID"]="'+firestats_site_id+'";$GLOBALS["FS_X-Forwarded-For"]="'+x_forwarded+'";include("'+firestats_location+'/php/firestats-hit.php");'
			#self.log.info('executing : ' + cmd)
			subprocess.call(['php','-r',cmd])
		return handler


	def post_process_request(self, req, template, content_type):
		return (template, content_type)
