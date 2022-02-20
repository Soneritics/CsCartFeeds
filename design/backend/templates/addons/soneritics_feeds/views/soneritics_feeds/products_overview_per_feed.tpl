{capture name="sidebar"}
    {notes title=__("notice")}
        <p>{__("addons.soneritics_feeds.products.sidebar")}</p>
        <p><a href="{"soneritics_feeds.products_overview_per_feed?download=true"|fn_url}">{__("addons.soneritics_feeds.global.download_this_overview")}</a></p>
    {/notes}
{/capture}

{capture name="mainbox"}
    <form action="" method="post" name="soneritics_products_form" id="soneritics_products_form">
        {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

        {if fn_get_runtime_company_id() == 0}
            <p class="no-items">{__("addons.soneritics_feeds.global.pick_a_company_first")}</p>
        {elseif $products}
            <table width="100%" class="table table-middle">
                <thead>
                <tr>
                    <th width="10%"><span>{__("image")}</span></th>
                    <th width="10%">{__("code")}</th>
                    <th width="65%">{__("name")}</th>
                    {foreach from=$availableFeeds item=feed}
                        <th class="left">{$feed}</th>
                    {/foreach}
                </tr>
                </thead>
                {foreach from=$products item=product}
                    <tr class="cm-row-status-{$product.status|lower} {$hide_inputs_if_shared_product}">
                        <td>
                            {include file="common/image.tpl" image=$product.main_pair.icon|default:$product.main_pair.detailed image_id=$product.main_pair.image_id image_width=$settings.Thumbnails.product_admin_mini_icon_width image_height=$settings.Thumbnails.product_admin_mini_icon_height}
                        </td>
                        <td>{$product.product_code}</td>
                        <td>
                            <a class="row-status" title="{$product.product|strip_tags}" href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a>
                        </td>
                        {foreach from=$availableFeeds item=feed key=feedId}
                            <td class="left">
                                <input type="hidden" name="all_products[]" value="{$product.product_id}" />
                                <input type="checkbox" name="active_products[{$feedId}][]" value="{$product.product_id}" class="checkbox cm-item"{if in_array($product.product_id, $activeProducts[$feedId])} checked="checked"{/if} />
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
            </table>

            {capture name="buttons"}
                {include file="buttons/save.tpl" but_name="dispatch[soneritics_feeds.products]" but_role="submit-button" but_target_form="soneritics_products_form"}
            {/capture}

            <div class="clearfix">
                {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
            </div>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    </form>
{/capture}

{include file="common/mainbox.tpl" title=__("addons.soneritics_feeds.global.complete_overview") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar content_id="manage_products"}
