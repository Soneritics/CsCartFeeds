{hook name="data_feeds:notice"}
{notes title=__("notice")}
    <p>Mogelijk hier nog wat informatie ofzo</p>
{/notes}
{/hook}

{capture name="mainbox"}
    <form action="{""|fn_url}" method="post" name="manage_datafeeds_form">
        {if $feeds}
            <table class="table sortable table-middle">
                <thead>
                    <tr>
                        <th width="39%" class="nowrap">{__("name")}</th>
                        <th width="25%" class="nowrap">{__("parser")}</th>
                        <th width="30%" class="nowrap">{__("company")}</th>
                        <th width="10%" class="nowrap">{__("language")}</th>
                        <th width="1%" class="nowrap">&nbsp;</th>
                    </tr>
                </thead>
                {foreach from=$feeds item=feed}
                    <tr>
                        <td><a href="{"soneritics_feeds.products?soneritics_feed_id=`$feed.id`"|fn_url}">{$feed.name}</a></td>
                        <td class="nowrap">{SoneriticsFeedParserFactory::getParserDisplayName($feed.parser)}</td>
                        <td class="nowrap">{fn_get_company_name($feed.company_id)}</td>
                        <td class="nowrap">{$feed.lang_code}</td>

                        <td class="nowrap">
                            {capture name="tools_list"}
                                <li>{btn type="list" text=__("link") href="index.php?dispatch=sfl.show&id=`$feed.id`" target="_blank"}</li>
                                <li class="divider"></li>
                                <li>{btn type="list" text=__("edit") href="soneritics_feeds.update?soneritics_feed_id=`$feed.id`"}</li>
                                <li>{btn type="list" text=__("delete") href="soneritics_feeds.delete?soneritics_feed_id=`$feed.id`"}</li>
                            {/capture}
                            <div class="hidden-tools">
                                {dropdown content=$smarty.capture.tools_list}
                            </div>
                        </td>
                    </tr>
                {/foreach}
            </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        {capture name="buttons"}
            {if $datafeeds}
                {capture name="tools_list"}
                    <li>{btn type="delete_selected" dispatch="dispatch[data_feeds.m_delete]" form="manage_datafeeds_form"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            {/if}
        {/capture}

        {capture name="adv_buttons"}
            {include file="common/tools.tpl" tool_href="soneritics_feeds.update" prefix="bottom" title="{__("add_datafeed")}" hide_tools=true icon="icon-plus"}
        {/capture}
    </form>
{/capture}
{include file="common/mainbox.tpl" title=__("data_feeds") content=$smarty.capture.mainbox tools=$smarty.capture.tools select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
