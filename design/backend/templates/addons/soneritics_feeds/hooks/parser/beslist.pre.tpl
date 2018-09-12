{foreach from=['stock_nl', 'not_in_stock_nl', 'stock_be', 'not_in_stock_be'] item=beslistProperty}
<div class="control-group">
    <label for="elm_beslist_{$beslistProperty}" class="control-label">{__("addons.soneritics_feeds.beslist.$beslistProperty")}:</label>
    <div class="controls">
        <input type="text" name="soneriticsFeed[data][beslist_{$beslistProperty}]" id="elm_beslist_{$beslistProperty}" size="55" value="{$soneriticsFeed.data["beslist_$beslistProperty"]}" class="input-text-large main-input" />
    </div>
</div>
{/foreach}