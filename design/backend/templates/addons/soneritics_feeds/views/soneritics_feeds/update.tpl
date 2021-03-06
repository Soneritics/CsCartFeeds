{if $soneriticsFeed.id}
    {assign var="id" value=$soneriticsFeed.id}
    {assign var="button_caption" value=__('update')}

    {notes title=__("notice")}
        <p>{__("addons.soneritics_feeds.update.sidebar")}</p>
    {/notes}
{else}
    {assign var="id" value=0}
    {assign var="button_caption" value=__('add')}

    {notes title=__("notice")}
        <p>{__("addons.soneritics_feeds.add.sidebar")}</p>
    {/notes}
{/if}

{capture name="mainbox"}
    <form method="post" name="feed_update_form" class="form-edit form-horizontal" enctype="multipart/form-data">
        <input type="hidden" name="soneriticsFeed[id]" value="{$id}" />

        <div id="content_detailed">
            <fieldset>
                <div class="control-group">
                    <label for="elm_datafeed_name" class="control-label cm-required">{__("name")}:</label>
                    <div class="controls">
                        <input type="text" name="soneriticsFeed[name]" id="elm_datafeed_name" size="55" value="{$soneriticsFeed.name}" class="input-text-large main-input" />
                    </div>
                </div>

                {foreach from=['lang_code' => $langs, 'company_id' => $companies, 'parser' => $parsers] key=i item=list}
                    <div class="control-group{if count($list) === 1} hidden{/if}">
                        <label class="control-label">{__("addons.soneritics_feeds.global.{$i}")}:</label>
                        <div class="controls">
                            <select name="soneriticsFeed[{$i}]" id="select-parser-{$i}">
                                {foreach from=$list item=v key=k}
                                    <option value="{$k}" {if $k == $soneriticsFeed[$i]}selected="selected"{/if}>{$v}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/foreach}

                {foreach from=$parsers item=parser}
                    <div id="parserdata-{$parser}" class="parser-extradata hidden">
                        {assign var=hookName value=strtolower("parser:$parser")}
                        {hook name=$hookName}{/hook}
                    </div>
                {/foreach}
            </fieldset>
        </div>

        {capture name="buttons"}
            {include file="buttons/save_cancel.tpl" hide_second_button=true but_name="dispatch[soneritics_feeds.update]" but_role="submit-link" but_target_form="feed_update_form" save=$id}
        {/capture}
    </form>

    {literal}
    <script>
        var showChosenParser = function() {
            $('.parser-extradata').addClass('hidden');
            var activeParser = $('#select-parser-parser').children('option:selected').text();
            $('#parserdata-' + activeParser).removeClass('hidden');
        };

        $(function() {
            $('#select-parser-parser').on('change', showChosenParser);
            showChosenParser();
        });
    </script>
    {/literal}
{/capture}

{include file="common/mainbox.tpl" title=$button_caption content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
