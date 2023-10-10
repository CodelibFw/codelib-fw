<?php
/**
 * CLConstants.php
 */
namespace cl\core;
/*
 * MIT License
 *
 * Copyright Codelib Framework (https://codelibfw.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

define('CSRFSTATUS', 'cl_csrf_status');
define('CLREQUEST', 1);
define('CLSESSION', 2);
define('CLAPP', 3);
define('CLRESPONSE', 4);
define('ALL', 5);
// signals that a certain feature is turned off
define('OFF', 0);
// if an appConfig entry (using addAppConfig()), is added with this key and it is true, all pages will be rendered, instead of the page
// with the current or active key. This would change the default behaviour of the app. You could use this, for
// instance, if you prefer not to pre-define your pages but to only add pages in your plugins as required.
define('RENDER_ALL', 'cl_render_all');
// if an appConfig entry (using addAppConfig()), is added with this key, specifying an implementation of the CLRenderer interface,
// CL will delegate to it to generate the final output, which must be return to CL as html to then be sent to the browser
define('APP_RENDERER', 'cl_app_renderer');
// if an appConfig entry (using addAppConfig()), is added with this key, specifying an implementation of the \cl\contract\CLDispatcher interface,
// CL will delegate to it to dispatch or route requests
define('APP_DISPATCHER', 'cl_app_renderer');
// if an appConfig entry is added with this key and it is true, programmatic controls defined with inline php code,
// will have that code evaluated (eval()) before rendering. By the default this is not the case, and instead, a
// variable replacement takes place. This eval behaviour can be risky, and should be avoided, unless you are sure
// it is harmless.
define('EVAL_INLINE_CODE', 'evalInlineCode');
define('CSRF_KEY', 'cl_xsrf_id');
define('CACHE_CFG', 'cl_cache_config');
define('CACHE_TYPE', 'cl_cache_type');
define('CACHE_SERVERS', 'cl_cache_servers');
define('NOCACHE', 0);
define('MEMCACHE', 1);
define('LOGLEVEL', 'cl_log_level');
define('LOGFOLDER', 'cl_log_folder');
define('LOGERROR', 2);
define('LOGINFO', 3);
define('LOGDEBUG', 4);
define('EXTENSIONS_FOLDER', 'cl_ext_folder');
define('CORS', 'cl_cors');
define('EMAIL_LIB', 'cl_email_lib');
define('MAIL_HOST', 'cl_mail_host');
define('AUTO_CONFIG', 'cl_auto_config');
define('ACTIVE_REPO', 'cl_active_repo');
define('SESSION_HANDLER', 'session_handler');
define('SESSION_HANDLER_TYPE', 'session_handler_type');
define('SESSION_HANDLER_CLASS', 'session_handler_class');
define('DB_SESSION_HANDLING', 'db_session_handling');
define('UPLOAD_CONFIG', 'cl_upload_cfg');
define('UPLOAD_DIR', 'cl_upload_dir');
define('UPLOAD_AUTOCONFIG', 'cl_upload_auto_cfg');
define('UPLOAD_FN', 'cl_upload_fn');
define('HTTP_GET', 'get');
define('HTTP_POST', 'post');
define('HTTP_PUT', 'put');
define('HTTP_HEAD', 'head');
define('HTTP_JSON', 'json');
define('CURRENT_TIMEZONE', 'cl_time_zone');
define('CLMYSQL', 'mysql');
define ('CLREDIS', 'clredis');
define('HTML_FILTER', 'html_filter');
define('FILTER_SPECIAL_CHARS', 'filter_spec_chars');
define('FILTER_REMOVE_TAGS', 'filter_tags');
define('STATUS_CODE', 'statusCode');
define('CL_PLUGIN', 'cl_plugin');
define('CL_EXEC_RETURN_CODE', 1);
define('CL_EXEC_RETURN_OUTPUT', 2);
define('CL_EXEC_RETURN_ALL_DATA', 3);
