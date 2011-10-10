package me.exote.webauction;

import java.net.MalformedURLException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.List;

import org.bukkit.Location;
import org.bukkit.block.Block;
import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.entity.Player;

public class WebAuctionCommands implements CommandExecutor{
	private final WebAuction plugin;
	
	public WebAuctionCommands(final WebAuction plugin) {
        this.plugin = plugin;
    }
	public static String MD5(String str) {
	    MessageDigest md = null;
	    try {
	      md = MessageDigest.getInstance("MD5");
	    } catch (NoSuchAlgorithmException e) {
	      e.printStackTrace();
	    }
	    md.update(str.getBytes());

	    byte[] byteData = md.digest();

	    StringBuffer hexString = new StringBuffer();
	    for (int i = 0; i < byteData.length; i++) {
	      String hex = Integer.toHexString(0xFF & byteData[i]);
	      if (hex.length() == 1) {
	        hexString.append('0');
	      }
	      hexString.append(hex);
	    }
	    return hexString.toString();
	  }
	@Override
    public boolean onCommand(CommandSender sender, Command command, String label, String[] split) {
		
		int params = split.length;
		Player player = (Player) sender;
		Location location = player.getLocation();
		List<Block> eyeList = player.getLineOfSight(null, 15);
		int i = 0;
		while ((eyeList.get(i).getTypeId()==0)&&(i < 15)){
			i++;
		}
		Block eyeLocBlock = eyeList.get(i);
		Location eyeLoc = eyeLocBlock.getLocation();
		int chestMatId = eyeLocBlock.getTypeId();
		String worldName = location.getWorld().getName();
		if (params == 0){
			return false;
		}
		else if (params == 1){
			return false;
		}
		else if (params == 2){
			
			if (split[0].equals("deposit")){
				if (split[1].equals("set")){
					if (chestMatId == 54){
						if (player.hasPermission("wa.create.chest.deposit")){
							try {
								if (!WebAuction.depositLocations.contains(eyeLoc)){
									WebAuction.depositLocations.add(eyeLoc);
									plugin.manageMySQL.initialize();
									String queryInsert = "INSERT INTO WA_DepositChests (world, x, y, z) VALUES ('" + eyeLoc.getWorld().getName() +"', "+eyeLoc.getX()+", "+eyeLoc.getY()+", "+eyeLoc.getZ()+");";
									this.plugin.manageMySQL.insertQuery(queryInsert);
									String queryInsertProt = "INSERT INTO WA_ProtectedLocations (world, x, y, z) VALUES ('" + eyeLoc.getWorld().getName() +"', "+eyeLoc.getX()+", "+eyeLoc.getY()+", "+eyeLoc.getZ()+");";
									this.plugin.manageMySQL.insertQuery(queryInsertProt);
									plugin.manageMySQL.close();
									WebAuction.log.info(WebAuction.logPrefix + "Deposit chest created");
									player.sendMessage(WebAuction.logPrefix + "Deposit chest created");
									return true;
								}else{
									player.sendMessage(WebAuction.logPrefix + "That is already a deposit chest");
									return true;
								}
							} catch (Exception e) {
								// TODO Auto-generated catch block
								e.printStackTrace();
							}
						}else{
							player.sendMessage(WebAuction.logPrefix + "You do not have permission to create deposit chests");
							return true;
						}
					}else{
			        	player.sendMessage(WebAuction.logPrefix + "That is not a chest");
			        	return true;
			        }
				} else if (split[1].equals("remove")){
					if (player.hasPermission("wa.remove.chest.deposit")){
						try {
							if (WebAuction.depositLocations.contains(eyeLoc)){
								WebAuction.depositLocations.remove(eyeLoc);
								plugin.manageMySQL.initialize();
								String queryRemove = "DELETE FROM WA_DepositChests WHERE world='" + eyeLoc.getWorld().getName() +"' AND x='"+eyeLoc.getX()+"' AND y='"+eyeLoc.getY()+"' AND z='"+eyeLoc.getZ()+"';";
								this.plugin.manageMySQL.insertQuery(queryRemove);
								plugin.manageMySQL.close();
								WebAuction.log.info(WebAuction.logPrefix + "Deposit chest removed");
								player.sendMessage(WebAuction.logPrefix + "Deposit chest removed");
								return true;
							}else{
								player.sendMessage(WebAuction.logPrefix + "No deposit chest found");
								return true;
							}
						} catch (Exception e) {
							// TODO Auto-generated catch block
							e.printStackTrace();
						}
					}else{
						player.sendMessage(WebAuction.logPrefix + "You do not have permission to remove that deposit chest");
						return true;
					}
				}
			} else if (split[0].equals("password")){
				if (split[1] != null){
					String newPass = MD5(split[1]);
					plugin.manageMySQL.initialize();
					String queryUpdate = "UPDATE WA_Players SET pass='"+newPass+"' WHERE name='"+player.getName()+"';";
					try {
						this.plugin.manageMySQL.updateQuery(queryUpdate);
						player.sendMessage(WebAuction.logPrefix + "Password changed");
						plugin.manageMySQL.close();
						return true;
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
					plugin.manageMySQL.close();
					return false;
				}
			}
		}
		return false;
	}
}