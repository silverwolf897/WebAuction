package me.exote.webauction;

import java.io.UnsupportedEncodingException;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.net.MalformedURLException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.LinkedList;

import org.bukkit.Location;
import org.bukkit.World;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.event.block.Action;
import org.bukkit.event.player.PlayerInteractEvent;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerListener;
import org.bukkit.inventory.Inventory;
import org.bukkit.inventory.ItemStack;
import org.getspout.spoutapi.block.SpoutChest;
import org.getspout.spoutapi.inventory.InventoryBuilder;
import org.getspout.spoutapi.player.SpoutPlayer;

import com.nijikokun.WA.register.payment.Method;
import com.nijikokun.WA.register.payment.Method.MethodAccount;
import com.nijikokun.WA.register.payment.Methods;


public class WebAuctionPlayerListener extends PlayerListener{

	private final WebAuction plugin;
	
	public WebAuctionPlayerListener(final WebAuction plugin) {
        this.plugin = plugin;
    }
	public static double round(double unrounded, int precision, int roundingMode)
	{
	    BigDecimal bd = new BigDecimal(unrounded);
	    BigDecimal rounded = bd.setScale(precision, roundingMode);
	    return rounded.doubleValue();
	}
	@Override
	public void onPlayerJoin(PlayerJoinEvent event) {
		plugin.manageMySQL.initialize();
		Player player = event.getPlayer();
		String playerName = player.getName();
		WebAuction.playerTimer.put(player, WebAuction.getCurrentMilli());
		String queryAdmins = "SELECT * FROM WA_WebAdmins WHERE name= '"+playerName+"';";
		String queryMail = "SELECT * FROM WA_Mail WHERE name= '"+playerName+"';";
		String queryAlerts = "SELECT * FROM WA_SaleAlerts WHERE seller= '"+playerName+"' AND alerted= 'false';";
		ResultSet resultAdmins = null;
		ResultSet resultMail = null;
		ResultSet resultAlerts = null;
		try {
			resultAlerts = this.plugin.manageMySQL.sqlQuery(queryAlerts);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		try {
			if (resultAlerts != null){
				while (resultAlerts.next()){	
					int id = resultAlerts.getInt("id");
					String buyer = resultAlerts.getString("buyer");
					String item = resultAlerts.getString("item");
					int quantity = resultAlerts.getInt("quantity");
					Double priceEach = resultAlerts.getDouble("price");
					Double priceTotal = quantity+priceEach;
					player.sendMessage(WebAuction.logPrefix + "You sold "+quantity+" "+item+" to "+buyer+" for "+priceEach+" each.");
					String updateAlerts = "UPDATE WA_SaleAlerts SET alerted = '1' WHERE id = '"+id+"';";
					this.plugin.manageMySQL.updateQuery(updateAlerts);
				}
			}
		}catch (Exception e){
			e.printStackTrace();
		}
		try {
			resultAdmins = this.plugin.manageMySQL.sqlQuery(queryAdmins);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {
			resultMail = this.plugin.manageMySQL.sqlQuery(queryMail);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		try {
			if ((resultMail != null)  && (resultMail.next())){
				player.sendMessage(WebAuction.logPrefix + "You have new mail!");	
			}
		}catch (Exception e){
			e.printStackTrace();
		}
		try {
			resultAdmins = this.plugin.manageMySQL.sqlQuery(queryAdmins);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		try {
			if ((resultAdmins != null)  && (resultAdmins.next())){
				if (player.hasPermission("wa.webadmin")){
					WebAuction.log.info(WebAuction.logPrefix + "Player is WebAdmin");
				}else{
					String queryDelete = "DELETE FROM WA_WebAdmins WHERE name='"+playerName+"';";
					WebAuction.manageMySQL.deleteQuery(queryDelete);
					WebAuction.log.info(WebAuction.logPrefix + "Player no longer WebAdmin");
				}
				
			} else{
				if (player.hasPermission("wa.webadmin")){
					WebAuction.log.info(WebAuction.logPrefix + "Player is a new WebAdmin, creating account");
					//create that person in database
				
					String queryInsert = "INSERT INTO WA_WebAdmins (name) VALUES ('" + player.getName() +"');";
					try {
						this.plugin.manageMySQL.insertQuery(queryInsert);
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}	
				}
			}
		}catch (Exception e){
			e.printStackTrace();
		}
		String query = "SELECT * FROM WA_Players WHERE name= '"+playerName+"';";
		ResultSet result = null;
		
		try {
			result = this.plugin.manageMySQL.sqlQuery(query);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		try {
			if ((result != null)  && (result.next())){
				WebAuction.log.info(WebAuction.logPrefix + "Player found");
				
			} else{
				WebAuction.log.info(WebAuction.logPrefix + "Player not found, creating account");
				//create that person in database
				String pass = WebAuctionCommands.MD5("Password");
				String queryInsert = "INSERT INTO WA_Players (name, pass, money) VALUES ('" + player.getName() +"', 'Password', " + 0 +");";
				try {
					this.plugin.manageMySQL.insertQuery(queryInsert);
				} catch (Exception e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}	
			}
		}catch (Exception e){
			e.printStackTrace();
		}
		plugin.manageMySQL.close();
	}
	public void onPlayerInteract(PlayerInteractEvent event) {
		if (event.getAction().equals(Action.RIGHT_CLICK_BLOCK)) {
		plugin.manageMySQL.initialize();
		Player player = event.getPlayer();
		String playerName = player.getName();
		Block block = event.getClickedBlock();
		MethodAccount account = Methods.getMethod().getAccount(playerName);
		if (block!=null){
			Location blockLoc = block.getLocation();
			int blockMat = block.getTypeId();
			
			if (blockMat == 54){
				//it's a chest
				if(WebAuction.depositLocations.contains(blockLoc)){
					//it's a deposit chest
					if (WebAuction.playerTimer.get(player) < WebAuction.getCurrentMilli()){
						WebAuction.playerTimer.put(player, WebAuction.getCurrentMilli()+ WebAuction.signDelay);
						if (player.hasPermission("wa.use.deposit.items")){
							if(WebAuction.chestUse.containsKey(blockLoc)){
								//it's in use
								player.sendMessage(WebAuction.logPrefix + "That deposit chest is in use");
								event.setCancelled(true);
							}else{
								//not in use
								WebAuction.chestUse.put(blockLoc, player);
								player.sendMessage(WebAuction.logPrefix + "Opened a WebAuction item deposit chest");
							}
						} else {
							player.sendMessage(WebAuction.logPrefix + "You do not have permission to use deposit chests");
							event.setCancelled(true);
						}
					}else{
						player.sendMessage(WebAuction.logPrefix + "Please wait a bit before using that again");
					}
				}
			}else if ((blockMat == 63)||(blockMat == 68)){
				//it's a sign
				Sign sign = (Sign)block.getState();
				String[] lines = sign.getLines();
				if (lines[0].equals("[WebAuction]")){
					if (WebAuction.playerTimer.get(player) < WebAuction.getCurrentMilli()){
						WebAuction.playerTimer.put(player, WebAuction.getCurrentMilli()+ WebAuction.signDelay);
						if (lines[1].equals("Deposit")){
							if (player.hasPermission("wa.use.deposit.money")){
								Double amount = Double.parseDouble(lines[2]);
								if(account.hasEnough(amount)){
									try {
										String query = "SELECT * FROM WA_Players WHERE name = '"+playerName+"'";
										ResultSet result = null;
										result = WebAuction.manageMySQL.sqlQuery(query);
										if (result.next()) {
											Double currentMoney = result.getDouble("money");
											currentMoney+=amount;
											currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
											player.sendMessage(WebAuction.logPrefix + "Added "+amount+" to auction account, new auction balance: "+ currentMoney);
											String queryUpdate = "UPDATE WA_Players SET money='"+currentMoney+"' WHERE name='"+playerName+"';";
											this.plugin.manageMySQL.updateQuery(queryUpdate);
											account.subtract(amount);
										} else {
											player.sendMessage(WebAuction.logPrefix + "No WebAuction account found, try logging off and back on again");
										}
									} catch (Exception e) {
										e.printStackTrace();
									}	
								}else{
									player.sendMessage(WebAuction.logPrefix + "You do not have enough money in your pocket.");
								}
							}			
						}else if (lines[1].equals("Withdraw")){
							if (player.hasPermission("wa.use.withdraw.money")){
								Double amount = 0.0;
								if (!lines[2].equals("All")){
									amount = Double.parseDouble(lines[2]);
								}
								try {
									String query = "SELECT * FROM WA_Players WHERE name = '"+playerName+"'";
									ResultSet result = null;
									result = WebAuction.manageMySQL.sqlQuery(query);
									if (result.next()) {
										// Match found!
										Double currentMoney = result.getDouble("money");
										if (lines[2].equals("All")){
											amount = currentMoney;
										}
										if (currentMoney >= amount){
											currentMoney-=amount;
											currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
											player.sendMessage(WebAuction.logPrefix + "Removed "+amount+" from auction account, new auction balance: "+ currentMoney);
											String queryUpdate = "UPDATE WA_Players SET money='"+currentMoney+"' WHERE name='"+playerName+"';";
											this.plugin.manageMySQL.updateQuery(queryUpdate);
											account.add(amount);
										}else{
											player.sendMessage(WebAuction.logPrefix + "You do not have enough money in your WebAuction account.");
										}
									} else {
										player.sendMessage(WebAuction.logPrefix + "No WebAuction account found, try logging off and back on again");
									}
								} catch (Exception e) {
									// TODO Auto-generated catch block
									e.printStackTrace();
								}
							} else {
								player.sendMessage(WebAuction.logPrefix + "You do not have permission to withdraw money");
								event.setCancelled(true);
							}
						}
						else if (lines[1].equals("MailBox")){
							if (player.hasPermission("wa.use.withdraw.items")){
								try {
									String query = "SELECT * FROM WA_Mail WHERE player = '"+playerName+"'";
									ResultSet result = null;
									result = WebAuction.manageMySQL.sqlQuery(query);
									if (result.next()) {
										// Match found!
										if (player.getInventory().firstEmpty() != -1){
											ItemStack stack = new ItemStack(result.getInt("name"), result.getInt("quantity"), result.getShort("damage"));
											int id = result.getInt("id");
											query = "DELETE FROM WA_Mail WHERE id='"+id+"';";
											WebAuction.manageMySQL.deleteQuery(query);
											player.getInventory().addItem(stack);
											player.updateInventory();
											player.sendMessage(WebAuction.logPrefix + "Mail retrieved");
										} else {
											player.sendMessage(WebAuction.logPrefix + "Inventory full, cannot get mail");
										}
									} else {
										player.sendMessage(WebAuction.logPrefix + "No mail");
									}
								} catch (Exception e) {
									// TODO Auto-generated catch block
									e.printStackTrace();
								}
							} else {
								player.sendMessage(WebAuction.logPrefix + "You do not have permission to use the mailbox");
								event.setCancelled(true);
							}
						}
					}else{
						player.sendMessage(WebAuction.logPrefix + "Please wait a bit before using that again");
					}
				}
			}
		}
	plugin.manageMySQL.close();
		}
	}
}
