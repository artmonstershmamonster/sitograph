{include file="$themePath/widget/header.tpl"}

<div class="container top-menu" hidden>
	<div class="row">
		{include file="$themePath/widget/menu-top.tpl"}
	</div>
</div>
<section>

<div class="container">

	<div class="row align-items-center justify-content-center" style="height: 100vh">
	
        <div class="col-md-4 text-center">
        	{include file="$themePath/widget/messages.tpl"}
        	
            {include file="$themePath/user/login.tpl"}
        </div>
        <div class="col-md-12 text-center">
            {$theme_copyright_text}
            
        </div>
	</div>
</div>

</section>

{include file="$themePath/widget/footer.tpl"}