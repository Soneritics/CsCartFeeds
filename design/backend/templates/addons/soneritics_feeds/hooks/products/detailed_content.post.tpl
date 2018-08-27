{if $availableFeeds}
    {include file="common/subheader.tpl" title="Data feeds" target="#soneritics_feeds"}
    <div id="soneritics_feeds">
        <table class="table sortable table-middle">
            {foreach from=$availableFeeds item=feed}
                <tr>
                    <td width="20">
                        <input
                            type="checkbox"
                            name="soneritics_feed_ids[]"
                            value="{$feed.id}"
                            {if in_array($feed.id, $activeFeeds)}checked="checked"{/if}
                            class="checkbox cm-item" />
                    </td>
                    <td class="nowrap">{$feed.name}</td>
                </tr>
            {/foreach}
        </table>
    </div>
{/if}