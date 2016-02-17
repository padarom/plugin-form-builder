{include file='header' pageTitle='wcf.acp.menu.link.examples.'|concat:$action}

<header class="boxHeadline">
    <h1>{lang}wcf.acp.examples.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
    <p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='ExampleAdd'}{/link}{else}{link controller='ExampleEdit' id=$primaryID}{/link}{/if}">
    <div class="container containerPadding marginTop">
        <fieldset>
            <legend>{lang}wcf.global.form.data{/lang}</legend>

            <dl{if $errorField == 'name'} class="formError"{/if}>
                <dt><label for="title">{lang}wcf.acp.example.title{/lang}</label></dt>
                <dd>
                    <input type="text" id="title" name="title" value="{if $primaryID}{$object->title}{/if}" required="required" autofocus="autofocus" class="long" />
                    {if $errorField == 'title'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}wcf.acp.user.example.title.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>

            <dl{if $errorField == 'description'} class="formError"{/if}>
                <dt><label for="description">{lang}wcf.acp.example.name{/lang}</label></dt>
                <dd>
                    <input type="text" id="description" description="name" value="{if $primaryID}{$object->description}{/if}" required="required" autofocus="autofocus" class="long" />
                    {if $errorField == 'description'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}wcf.acp.user.example.description.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>

            <dl {if $errorField == 'isDisabled'} class="formError"{/if}>
                <dt></dt>
                <dd>
                    <label><input type="checkbox" id="isDisabled" name="isDisabled" value="1"{if $primaryID && $object->isDisabled} checked="checked"{/if} /> {lang}wcf.acp.example.isDisabled{/lang}</label>
                </dd>
            </dl>

        {event name='fieldsets'}
    </div>

    <div class="formSubmit">
        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
        {@SECURITY_TOKEN_INPUT_TAG}
    </div>
</form>

{include file='footer'}