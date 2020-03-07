<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>{$title}</title>
		<style>
			html,body,div,span,object,iframe,
			h1,h2,h3,h4,h5,h6,p,blockquote,pre,
			abbr,address,cite,code,
			del,dfn,em,img,ins,kbd,q,samp,
			small,strong,sub,sup,var,
			b,i,
			dl,dt,dd,ol,ul,li,
			fieldset,form,label,legend,
			table,caption,tbody,tfoot,thead,tr,th,td,
			article,aside,canvas,details,figcaption,figure,
			footer,header,hgroup,menu,nav,section,summary,
			time,mark,audio,video{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;}
			body{line-height:1;}
			article,aside,details,figcaption,figure,
			footer,header,hgroup,menu,nav,section{display:block;}
			nav ul{list-style:none;}
			blockquote,q{quotes:none;}
			blockquote:before,blockquote:after,
			q:before,q:after{content:'';content:none;}
			a{margin:0;padding:0;font-size:100%;vertical-align:baseline;background:transparent;}
			ins{background-color:#ff9;color:#000;text-decoration:none;}
			mark{background-color:#ff9;color:#000;font-style:italic;font-weight:bold;}
			del{text-decoration:line-through;}
			abbr[title],dfn[title]{border-bottom:1px dotted;cursor:help;}
			table{border-collapse:collapse;border-spacing:0;}
			hr{display:block;height:1px;border:0;border-top:1px solid #cccccc;margin:1em 0;padding:0;}
			input,select{vertical-align:middle;}
			html{ background: #EDEDED; height: 100%; }
			body{background:#FFF;margin:0 auto;min-height:100%;padding:0 30px;width:440px;color:#666;font:14px/23px Arial,Verdana,sans-serif;}
			h1,h2,h3,p,ul,ol,form,section{margin:0 0 20px 0;}
			h1{color:#333;font-size:20px;}
			h2,h3{color:#333;font-size:14px;}
			h3{margin:0;font-size:12px;font-weight:bold;}
			ul,ol{list-style-position:inside;color:#999;}
			ul{list-style-type:square;}
			code,kbd{background:#EEE;border:1px solid #DDD;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:0 4px;color:#666;font-size:12px;}
			pre{background:#EEE;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:5px 10px;color:#666;font-size:12px;}
			pre code{background:transparent;border:none;padding:0;}
			.green, a, h2 { color: #038f03; }
			header{padding: 30px 0;text-align:center;}
		</style>
	</head>
	<body>
		<header>
			<a href="https://leaf-docs.netlify.com" style="text-decoration: none">
				<img src="https://leaf-docs.netlify.com/images/logo.png" alt="Leaf PHP Framework" style="width: 110px;">
			</a>
		</header>
		<h1>
			{autoescape="off"}
				{$welcome}
			{/autoescape}
		</h1>
		<p>
			Congratulations! Your Leaf application is running. If this is
			your first time using Leaf, start with this <a href="https://leaf-docs.netlify.com/v2.0/introduction/getting-started.html" target="_blank">"Hello World" Tutorial</a>.
		</p>
		<section>
			<h2>Get Started</h2>
			<ol>
				<li>The application code is in <code>index.php</code></li>
				<li>Read the <a href="https://leaf-docs.netlify.com/" target="_blank">docs</a></li>
				<li>Follow <a href="http://www.twitter.com/leafphp" target="_blank">@leafphp</a> on Twitter</li>
			</ol>
		</section>
		<section>
			<h2>Leaf Framework Community</h2>

			<h3>Join our community on slack</h3>
			<p>
				Visit the <a href="https://join.slack.com/t/leafphp/shared_invite/enQtNzg5MDU1NDMzMTg2LWQzMDJlNWMzOGVkN2FkNTQ0YWFkNTY0NWYxYzY1NzA0MjU1MDFmYjY4Nzg3ZTNiYWYyNThlOWE5MmI1MTNmODE" target="_blank">Leaf support forum and knowledge base</a>
				to read announcements, chat with fellow Leaf users, ask questions, help others, or show off your cool
				Leaf Framework apps.
			</p>

			<h3>Twitter</h3>
			<p>
				Follow <a href="http://www.twitter.com/leafphp" target="_blank">@leafphp</a> on Twitter to receive the very latest news
				and updates about the framework.
			</p>
		</section>
		<section style="padding-bottom: 20px">
			<h2>Other Leaf Projects</h2>
			<p>
				Other Leaf projects include Templating with Leaf Veins, Leaf MVC and Leaf API
			</p>
			<p><a href="https://leaf-docs.netlify.com/v1.5.0/projects.html" target="_blank">View other Leaf projects</a></p>
		</section>
	</body>
</html>