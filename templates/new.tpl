{include file='header.tpl' title='New'}
<form action="{$web_root}/" method="post">
	<table>
	{if isset($badCaptcha) && $badCaptcha === True}
	<tr>
		<th>&nbsp;</th>
		<td>
			Sorry, the captcha answer supplied was bad!
		</td>
	</tr>
	{/if}
	<tr>
		<th>
			<label for="content">Content:</label>
		</th>
		<td>
			<textarea rows="40" cols="100" name="content" id="content">{if isset($content)}{$content}{/if}</textarea>
		</td>
	</tr>
	<tr>
		<th>
			<label for="syntax">Syntax:</label>
		</th>
		<td>
			<select name="syntax" id="syntax">
				<option value="" selected="selected">None</option>
				<option value="text">Plain Text</option> 
				<option value="apacheconf">ApacheConf</option> 
				<option value="as">ActionScript</option> 
				<option value="bash">Bash</option> 
				<option value="bat">Batchfile</option> 
				<option value="bbcode">BBCode</option> 
				<option value="befunge">Befunge</option> 
				<option value="boo">Boo</option> 
				<option value="c">C</option> 
				<option value="c-objdump">c-objdump</option> 
				<option value="common-lisp">Common Lisp</option> 
				<option value="control">Debian Control file</option> 
				<option value="cpp">C++</option> 
				<option value="cpp-objdump">cpp-objdump</option> 
				<option value="csharp">C#</option> 
				<option value="css">CSS</option> 
				<option value="css+django">CSS+Django/Jinja</option> 
				<option value="css+erb">CSS+Ruby</option> 
				<option value="css+genshitext">CSS+Genshi Text</option> 
				<option value="css+mako">CSS+Mako</option> 
				<option value="css+myghty">CSS+Myghty</option> 
				<option value="css+php">CSS+PHP</option> 
				<option value="css+smarty">CSS+Smarty</option> 
				<option value="d">D</option> 
				<option value="d-objdump">d-objdump</option> 
				<option value="delphi">Delphi</option> 
				<option value="diff">Diff</option> 
				<option value="django">Django/Jinja</option> 
				<option value="dylan">DylanLexer</option> 
				<option value="erb">ERB</option> 
				<option value="erlang">Erlang</option> 
				<option value="gas">GAS</option> 
				<option value="genshi">Genshi</option> 
				<option value="genshitext">Genshi Text</option> 
				<option value="groff">Groff</option> 
				<option value="haskell">Haskell</option> 
				<option value="html">HTML</option> 
				<option value="html+django">HTML+Django/Jinja</option> 
				<option value="html+genshi">HTML+Genshi</option> 
				<option value="html+mako">HTML+Mako</option> 
				<option value="html+myghty">HTML+Myghty</option> 
				<option value="html+php">HTML+PHP</option> 
				<option value="html+smarty">HTML+Smarty</option> 
				<option value="ini">INI</option> 
				<option value="irc">IRC logs</option> 
				<option value="java">Java</option> 
				<option value="js">JavaScript</option> 
				<option value="js+django">JavaScript+Django/Jinja</option> 
				<option value="js+erb">JavaScript+Ruby</option> 
				<option value="js+genshitext">JavaScript+Genshi Text</option> 
				<option value="js+mako">JavaScript+Mako</option> 
				<option value="js+myghty">JavaScript+Myghty</option> 
				<option value="js+php">JavaScript+PHP</option> 
				<option value="js+smarty">JavaScript+Smarty</option> 
				<option value="jsp">Java Server Page</option> 
				<option value="lhs">Literate Haskell</option> 
				<option value="llvm">LLVM</option> 
				<option value="lua">Lua</option> 
				<option value="make">Makefile</option> 
				<option value="mako">Mako</option> 
				<option value="minid">MiniD</option> 
				<option value="moocode">MOOCode</option> 
				<option value="mupad">MuPAD</option> 
				<option value="myghty">Myghty</option> 
				<option value="mysql">MySQL</option> 
				<option value="objdump">objdump</option> 
				<option value="objective-c">Objective-C</option> 
				<option value="ocaml">OCaml</option> 
				<option value="perl">Perl</option> 
				<option value="php">PHP</option> 
				<option value="pot">Gettext Catalog</option> 
				<option value="pycon">Python console session</option> 
				<option value="pytb">Python Traceback</option> 
				<option value="python">Python</option> 
				<option value="raw">Raw token data</option> 
				<option value="rb">Ruby</option> 
				<option value="rbcon">Ruby irb session</option> 
				<option value="redcode">Redcode</option> 
				<option value="rhtml">RHTML</option> 
				<option value="rst">reStructuredText</option> 
				<option value="scheme">Scheme</option> 
				<option value="smarty">Smarty</option> 
				<option value="sourceslist">Debian Sourcelist</option> 
				<option value="sql">SQL</option> 
				<option value="squidconf">SquidConf</option> 
				<option value="tex">TeX</option> 
				<option value="text">Text only</option> 
				<option value="trac-wiki">MoinMoin/Trac Wiki markup</option> 
				<option value="vb.net">VB.net</option> 
				<option value="vim">VimL</option> 
				<option value="xml">XML</option> 
				<option value="xml+django">XML+Django/Jinja</option> 
				<option value="xml+erb">XML+Ruby</option> 
				<option value="xml+mako">XML+Mako</option> 
				<option value="xml+myghty">XML+Myghty</option> 
				<option value="xml+php">XML+PHP</option> 
				<option value="xml+smarty">XML+Smarty</option> 
			</select>
		</td>
	</tr> 
	{if isset($recapthca_box)}
	<tr>
		<th>Are you human?</th>
		<td>
			{$recaptcha_box}
		</td>
	</tr>
	{/if}
	<tr>
		<th>&nbsp;</th>
		<td>
			<input type="submit" value="Paste!" />
		</td>
	</tr>
	</table>
</form>
{include file='footer.tpl'}
