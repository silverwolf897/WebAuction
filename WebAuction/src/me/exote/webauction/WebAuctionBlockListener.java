package me.exote.webauction;

import java.net.MalformedURLException;
import java.sql.ResultSet;

import org.bukkit.Location;
import org.bukkit.World;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.event.block.BlockBreakEvent;
import org.bukkit.event.block.BlockListener;
import org.bukkit.event.block.SignChangeEvent;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;
import com.nijikokun.WA.register.payment.Methods;

public class WebAuctionBlockListener extends BlockListener{
	
	private final WebAuction plugin;
	
	public WebAuctionBlockListener(final WebAuction plugin) {
        this.plugin = plugin;
    }
	@Override
	public void onBlockBreak(BlockBreakEvent event){
		Location loc = event.getBlock().getLocation();
		Block block = event.getBlock();
		Player player = event.getPlayer();
		World world = block.getWorld();
		if ((block.getTypeId() == 54)||(block.getTypeId() == 63)||(block.getTypeId() == 68)){
			String query = "SELECT * FROM WA_ProtectedLocations WHERE world='"+world.getName()+"' AND x='"+block.getX()+"' AND y='"+block.getY()+"' AND z='"+block.getZ()+"'";
			ResultSet result = null;
		
			try {
				plugin.manageMySQL.initialize();
				result = plugin.manageMySQL.sqlQuery(query);
				
				if (result.next()){
					if (!player.hasPermission("wa.remove")){
						event.setCancelled(true);
						player.sendMessage(WebAuction.logPrefix + "You do not have permission to remove that");
					}else{
						String queryRemove = "DELETE FROM WA_DepositChests WHERE world='"+world.getName()+"' AND x='"+block.getX()+"' AND y='"+block.getY()+"' AND z='"+block.getZ()+"'";
						this.plugin.manageMySQL.insertQuery(queryRemove);
						String queryRemoveSign = "DELETE FROM WA_ShoutSigns WHERE world='"+world.getName()+"' AND x='"+block.getX()+"' AND y='"+block.getY()+"' AND z='"+block.getZ()+"'";
						this.plugin.manageMySQL.insertQuery(queryRemove);
						if(WebAuction.shoutSigns.containsKey(loc)){
							WebAuction.shoutSigns.remove(loc);
						}
						String queryRemoveLoc = "DELETE FROM WA_ProtectedLocations WHERE world='"+world.getName()+"' AND x='"+block.getX()+"' AND y='"+block.getY()+"' AND z='"+block.getZ()+"'";
						this.plugin.manageMySQL.insertQuery(queryRemoveLoc);
						WebAuction.log.info(WebAuction.logPrefix + "Removed");
						player.sendMessage(WebAuction.logPrefix + "Removed");
					}
					plugin.manageMySQL.close();
				}
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
	}
    public void onSignChange(SignChangeEvent event){
    	String[] lines = event.getLines();
    	Player player = event.getPlayer();
    	Block sign = event.getBlock();
    	World world = sign.getWorld();
    	Boolean allowEvent = false;
    	if (player != null){
    	if (lines[0].equals("[WebAuction]")){
    		if (lines[1].equals("Shout")){
    			if (player.hasPermission("wa.create.sign.shout")){
    				allowEvent = true;
    				player.sendMessage(WebAuction.logPrefix + "Shout sign created.");
    				int radius = Integer.parseInt(lines[2]);
    				plugin.manageMySQL.initialize();
    				try{
    					WebAuction.shoutSigns.put(sign.getLocation(), radius);
    					String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertProt);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
        				String queryInsertRec = "INSERT INTO WA_ShoutSigns (world, radius, x, y, z) VALUES ('" + world.getName() +"', "+radius+", "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertRec);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
    				} catch (Exception e) {
    					// TODO Auto-generated catch block
    					e.printStackTrace();
    				}
    				plugin.manageMySQL.close();
    			
    			}
    		}
    		if (lines[1].equals("Recent")){
    			if (player.hasPermission("wa.create.sign.recent")){
    				allowEvent = true;
    				player.sendMessage(WebAuction.logPrefix + "Recent auction sign created.");
    				int amount = Integer.parseInt(lines[2]);
    				if (amount <= WebAuction.lastAuction){
    				plugin.manageMySQL.initialize();
    				String recentQuery = "SELECT * FROM WA_Auctions ORDER BY id DESC;";
    				ResultSet result = null;
    				try{
    					result = plugin.manageMySQL.sqlQuery(recentQuery);
    					int counter = 0;
    					while (counter < amount)
    					{
    					  if (result.next()) {
    						counter++;
    					  }
    					}
    					ItemStack stack = new ItemStack(result.getInt("name"), result.getInt("quantity"), result.getShort("damage"));
    					int quant = result.getInt("quantity");
    					Double price = result.getDouble("price");
    					String formattedPrice = Methods.getMethod().format(price);
    					WebAuction.recentSigns.put(sign.getLocation(), amount);
    					String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertProt);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
        				String queryInsertRec = "INSERT INTO WA_RecentSigns (world, offset, x, y, z) VALUES ('" + world.getName() +"', "+amount+", "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertRec);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
    					event.setLine(1, stack.getType().toString());
    					event.setLine(2, quant+"");
    					event.setLine(3, ""+formattedPrice);
    				} catch (Exception e) {
    					// TODO Auto-generated catch block
    					e.printStackTrace();
    				}
    				plugin.manageMySQL.close();
    				}else{
    					plugin.manageMySQL.initialize();
    					WebAuction.recentSigns.put(sign.getLocation(), amount);
	    				event.setLine(1, "Recent");
	    				event.setLine(2, amount+"");
	    				event.setLine(3, "Not Available");
	    				String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertProt);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
        				String queryInsertRec = "INSERT INTO WA_RecentSigns (world, offset, x, y, z) VALUES ('" + world.getName() +"', "+amount+", "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
        				try {
        					plugin.manageMySQL.insertQuery(queryInsertRec);
        				} catch (Exception e) {
        					// TODO Auto-generated catch block
        					e.printStackTrace();
        				}
        				plugin.manageMySQL.close();
			    	}
    			
    			}
    		}
    		if (lines[1].equals("Deposit")){
    			if (player.hasPermission("wa.create.sign.deposit")){
    				allowEvent = true;
    				player.sendMessage(WebAuction.logPrefix + "Deposit point created");
    				plugin.manageMySQL.initialize();
    				String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
    				try {
    					plugin.manageMySQL.insertQuery(queryInsertProt);
    				} catch (Exception e) {
    					// TODO Auto-generated catch block
    					e.printStackTrace();
    				}
    				plugin.manageMySQL.close();
    			}
    			
    		}
    		if (lines[1].equals("Withdraw")){
    			if (player.hasPermission("wa.create.sign.withdraw")){
    				allowEvent = true;
    				player.sendMessage(WebAuction.logPrefix + "Withdraw point created");
    				plugin.manageMySQL.initialize();
    				String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
    				try {
    					plugin.manageMySQL.insertQuery(queryInsertProt);
    				} catch (Exception e) {
    					// TODO Auto-generated catch block
    					e.printStackTrace();
    				}
    				plugin.manageMySQL.close();
    			}
    		}
    		if (lines[1].equals("MailBox")){
    			if (player.hasPermission("wa.create.sign.mailbox")){	        
    				allowEvent = true;
    				player.sendMessage(WebAuction.logPrefix + "MailBox created");
    				plugin.manageMySQL.initialize();
    				String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + world.getName() +"', "+sign.getX()+", "+sign.getY()+", "+sign.getZ()+");";
    				try {
    					plugin.manageMySQL.insertQuery(queryInsertProt);
    				} catch (Exception e) {
    					// TODO Auto-generated catch block
    					e.printStackTrace();
    				}
    				plugin.manageMySQL.close();    
    			}
    		}
    		if (allowEvent == false){
    			event.setCancelled(true);
    			sign.setTypeId(0);
    			ItemStack stack = new ItemStack(323, 1);
    			player.getInventory().addItem(stack);
    			player.sendMessage(WebAuction.logPrefix + "You do not have permission");
    		}
    	}
    	}
    }
	
}
