package me.exote.webauction;

import org.bukkit.event.server.PluginDisableEvent;
import org.bukkit.event.server.PluginEnableEvent;
import org.bukkit.event.server.ServerListener;

import com.nijikokun.WA.register.payment.Methods;

public class server extends ServerListener {
	 private WebAuction plugin;
	 private Methods Methods = null;

	 public server(WebAuction webAuction) {
	     this.plugin = webAuction;
	     this.Methods = new Methods();
	 }

    @Override
    public void onPluginDisable(PluginDisableEvent event) {
    	if (this.Methods != null && this.Methods.hasMethod()) {
            Boolean check = this.Methods.checkDisabled(event.getPlugin());

            if(check) {
                Methods.reset();
                System.out.println("[" + plugin.info.getName() + "] Payment method was disabled. No longer accepting payments.");
            }
        }
    }

    @Override
    public void onPluginEnable(PluginEnableEvent event) {
    	if (!this.Methods.hasMethod()) {
            if(this.Methods.setMethod(plugin.getServer().getPluginManager())) {
                if(this.Methods.hasMethod())
                    System.out.println("[" + plugin.info.getName() + "] Payment method found (" + this.Methods.getMethod().getName() + " version: " + this.Methods.getMethod().getVersion() + ")");
            }
        }
    }
}