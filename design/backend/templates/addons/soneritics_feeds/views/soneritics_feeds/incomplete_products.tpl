{capture name="sidebar"}
    {notes title=__("notice")}
        <p>{__("addons.soneritics_feeds.incomplete_products.sidebar")}</p>
    {/notes}
{/capture}

{capture name="mainbox"}
    <form action="" method="post" name="soneritics_products_form" id="soneritics_products_form">
        {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

        {if $products}
            <table width="100%" class="table table-middle">
                <thead>
                <tr>
                    <th width="15%"><span>{__("image")}</span></th>
                    <th width="10%">{__("code")}</th>
                    <th width="75%">{__("name")}</th>
                </tr>
                </thead>
                {foreach from=$products item=product}
                    <tr class="cm-row-status-{$product.status|lower} {$hide_inputs_if_shared_product}">
                        <td>
                            <input type="hidden" name="all_products[]" value="{$product.product_id}" />
                            {include file="common/image.tpl" image=$product.main_pair.icon|default:$product.main_pair.detailed image_id=$product.main_pair.image_id image_width=$settings.Thumbnails.product_admin_mini_icon_width image_height=$settings.Thumbnails.product_admin_mini_icon_height}
                        </td>
                        <td>{$product.product_code}</td>
                        <td>
                            <a class="row-status" title="{$product.product|strip_tags}" href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        <div class="clearfix">
            {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
        </div>
    </form>
{/capture}

{include file="common/mainbox.tpl" title=__("addons.soneritics_feeds.manage.incomplete_products") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar content_id="manage_products"}
