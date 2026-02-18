<h2>{$MOD.LBL_PLUGIN_LOGS}</h2>

<!-- Button bar -->
<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
    <!-- Left buttons -->
    <div>
        <button class="btn btn-primary" onclick="view_settings();">Back to Settings</button>
        <button class="btn btn-secondary" onclick="reload_logs();">Reload Logs</button>
    </div>

    <!-- Right button -->
    <div>
        <button class="btn btn-success" onclick="download_logs();">Download Logs</button>
        <button class="btn btn-danger" onclick="clear_logs();">Clear Logs</button>
    </div>
</div>

<!-- Log viewer -->
<div style="padding:10px; border:1px solid #ccc; background:#f9f9f9; max-height:400px; overflow-y:scroll;">
    {if isset($logs) && $logs|@count > 0}
        <pre style="margin:0;">
{foreach from=$logs item=logLine}
    {$logLine}
{/foreach}
        </pre>
    {else}
        <p>No logs found.</p>
    {/if}
</div>

<!-- Scripts -->
{literal}
    <script>
        function view_settings() {
            var app = window.parent.SUGAR.App;
            app.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=config", {trigger: true, replace: true});
        }

        function clear_logs() {
            var app = window.parent.SUGAR.App;
            app.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=clearlogs", {trigger: true, replace: true});
        }
        function reload_logs() {
            location.reload();
        }

        function download_logs() {
            window.open("index.php?module=TL_ActiveCampaigns&action=downloadlog", "_blank");
        }
    </script>
{/literal}
