{capture name="sidebar"}
    {notes title=__("notice")}
        <p>Mogelijk hier nog wat informatie ofzo</p>
    {/notes}
{/capture}

{capture name="mainbox"}
    <form action="{""|fn_url}" method="post" name="soneritics_products_form" id="soneritics_products_form">
        {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

        {if $products}
            <table width="100%" class="table table-middle">
                <thead>
                <tr>
                    <th class="left">{include file="common/check_items.tpl" check_statuses=''|fn_get_default_status_filters:true}</th>
                    <th width="10%"><span>{__("image")}</span></th>
                    <th width="10%">{__("code")}</th>
                    <th width="65%">{__("name")}</th>
                    <th width="15%" align="right">{__("list_price")}</th>
                </tr>
                </thead>
                {foreach from=$products item=product}
                    <tr class="cm-row-status-{$product.status|lower} {$hide_inputs_if_shared_product}">
                        <td class="left">
                            <input type="hidden" name="all_products[]" value="{$product.product_id}" />
                            <input type="checkbox" name="active_products[]" value="{$product.product_id}" class="checkbox cm-item" />
                        </td>
                        <td>
                            {include file="common/image.tpl" image=$product.main_pair.icon|default:$product.main_pair.detailed image_id=$product.main_pair.image_id image_width=$settings.Thumbnails.product_admin_mini_icon_width image_height=$settings.Thumbnails.product_admin_mini_icon_height}
                        </td>
                        <td>{$product.product_code}</td>
                        <td>{$product.product}</td>
                        <td align="right">{$product.list_price|fn_format_price:$primary_currency:null:false}</td>
                    </tr>
                {/foreach}
            </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        {capture name="buttons"}
            {if $products}
                {include file="buttons/save.tpl" but_name="dispatch[soneritics_feeds.products]" but_role="submit-button" but_target_form="soneritics_products_form"}
            {/if}
        {/capture}

        <div class="clearfix">
            {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
        </div>
    </form>
{/capture}

{include file="common/mainbox.tpl" title=__("products") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar content_id="manage_products"}
