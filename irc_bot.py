#!/usr/bin/env python
'''
 This file is part of PasteNutter.

 PasteNutter is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 PasteNutter is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with PasteNutter.  If not, see <http://www.gnu.org/licenses/>. 
'''
from twisted.internet.protocol import DatagramProtocol
from twisted.words.protocols import irc
from twisted.internet import protocol, reactor
from twisted.python import log
import thread
import simplejson as json
import logging
import socket
import random
import MySQLdb
import time

paste_url = "http://apps.damianzaremba.co.uk/PasteNutter/"
IRC_SERVER = 'irc.minecraftirc.net'
IRC_PORT = 6667
IRC_USER = "PasteBot"
IRC_NS_PASS = ""
IRC_CHANNELS = [
	('#mcpaste', ''),
]
RC_IP = "127.0.0.1"
RC_PORT = 4398
RC_LIMIT_MIN = 2
RC_LIMIT_HOUR = 10
DB_HOST = "127.0.0.1"
DB_USER = "PasteNutter"
DB_PASS = ""
DB_SCHEME = "PasteNutter"

logging.basicConfig()
logger = logging.getLogger('IRCBot')
logger.setLevel(logging.DEBUG)

class Database:
	conn = None

	def escape_string(self, string):
		try:
			return self.conn.escape_string(string)
		except (AttributeError, MySQLdb.OperationalError):
			self.connect()
			return self.conn.escape_string(string)

	def connect(self):
		logger.info("Connecting to mysql")
		self.conn = MySQLdb.connect(host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_SCHEME)

	def cursor(self):
		try:
			return self.conn.cursor()
		except (AttributeError, MySQLdb.OperationalError):
			self.connect()
			return self.conn.cursor()

	def check_limit(self, user):
		logger.debug('check_limit called')

		if RC_LIMIT_MIN == False:
			logger.debug('RC_LIMIT_MIN disabled')
		else:
			logger.debug('RC_LIMIT_MIN running')

			tlimit = str(int(time.time()) - 60)
			query = "SELECT COUNT(*) FROM `pastes` WHERE `user` = '%s' AND `time` > '%s'" %
			(self.escape_string(tlimit), self.escape_string(user))
			logger.debug("Running query: %s" % query)
			cur = self.cursor()
			cur.execute(query)
			row = cur.fetchone()
			count = int(row[0])
			cur.close()

			if count > RC_LIMIT_MIN:
				return False

		if RC_LIMIT_HOUR == False:
			logger.debug('RC_LIMIT_HOUR disabled')
		else:
			logger.debug('RC_LIMIT_HOUR running')

			tlimit = str(int(time.time()) - 3600)
			query = "SELECT COUNT(*) FROM `pastes` WHERE `user` = '%s' AND `time` > '%s'" %
			(self.escape_string(tlimit), self.escape_string(user))
			logger.debug("Running query: %s" % query)
			cur = self.cursor()
			cur.execute(query)
			row = cur.fetchone()
			count = int(row[0])
			cur.close()

			if count > RC_LIMIT_HOUR:
				return False
		return True

	def pong(self):
		ptime = str(int(time.time()))
		logger.debug('db.pong called')
		query = "UPDATE `irc_users` SET `ping` = '%s'" % self.escape_string(ptime)
		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

	def add_user(self, nick, ip):
		logger.debug('db.add_user called with %s, %s' % (nick, ip))
		query = "INSERT IGNORE INTO `irc_users` SET `nick` = '%s', `host` = '%s'" % (
								self.escape_string(nick),
								self.escape_string(ip))
		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

		self.pong()

	def update_user(self, nick, host):
		logger.debug('db.update_user called with %s, %s' % (nick, host))
		query = "UPDATE `irc_users` SET `host` = '%s' WHERE `nick` = '%s'" % (
								self.escape_string(host),
								self.escape_string(nick))

		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

		self.pong()

	def rename_user(self, old_nick, new_nick):
		logger.debug('db.rename_user called with %s, %s' % (old_nick, new_nick))
		query = "UPDATE `irc_users` SET `nick` = '%s' WHERE `nick` = '%s'" % (
								self.escape_string(new_nick),
								self.escape_string(old_nick))
		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

		self.pong()

	def remove_user(self, nick):
		logger.debug('db.remove_user called with %s' % nick)
		query = "DELETE FROM `irc_users` WHERE `nick` = '%s'" % self.escape_string(nick)
		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

		self.pong()

	def clean(self):
		logger.debug('db.clean called')
		query = "DELETE FROM `irc_users`"
		logger.debug("Running query: %s" % query)
		cur = self.cursor()
		cur.execute(query)
		self.conn.commit()
		cur.close()

		self.pong()

class WebNotify(DatagramProtocol):
	def __init__(self):
		self.callback = None
	
	def datagramReceived(self, data, (host, port)):
		logger.info("WebNotify got connection from %s:%d" % (host, port))

		try:
			sdata = json.loads(data.strip())
		except Exception, e:
			logger.info("Bad data rev'd")
			return

		if "user" in sdata and "url" in sdata:
			user = sdata["user"]
			url = sdata["url"]

			format = False
			if "format" in sdata and len(str(sdata['format'])) > 0:
				format = sdata["format"]

			self.callback(user, url, format)

class IRCBotProtocol(irc.IRCClient):
	nickname = IRC_USER
	channels = {}

	def __init__(self, join_channels):
		self.join_channels = join_channels
		self.db = Database()
	
	def webnotify_callback(self, user, link, format=False):
		msg = "%s pasted %s" % (user, link)
		if format:
			msg += " (%s)" % format

		if len(msg) > 140:
			msg = "%s [...]" % msg[135:]

		for channel in self.channels:
			if self.channels[channel] == True:
				if self.db.check_limit(user):
					logger.debug("Sending '%s' to %s" % (msg, channel))
					self.msg(str(channel), str(msg))
			else:
				logger.info("Skipping '%s' to '%s' due to limits" % (msg, channel))

	def signedOn(self):
		self.factory.webnotify.callback = self.webnotify_callback
		for channel, password in self.join_channels:
			logger.info("Joining %s (%s)" % (channel, password))
			self.join(channel, password)

		logger.debug("Setting ourselves to +B")
		self.mode(self.nickname, True, '+B', limit=None, user=self.nickname)

		logger.debug("Identifying ourselves to nickserv")
		self.msg("NickServ", "IDENTIFY %s" % IRC_NS_PASS)

		logger.info("Signed on")

	def joined(self, channel):
		self.channels[channel] = False
		logger.info("Joined %s" % channel)
		self.who(channel)
	
	def kickedFrom(self, channel, kicker, message):
		self.channels.remove(channel)
		logger.info("Kicked from %s" % channel)
	
	def nickChanged(self, nick):
		logger.info("Nick changed to %s" % nick)

	def privmsg(self, user, channel, msg):
		user = user.split('!', 1)[0]
		mparts = msg.split(" ")

		if channel[0] == "#":
			if mparts[0] == "!pb" or mparts[0] == "!paste":
				logger.debug("!pb called")
				self.msg(channel, "Paste here: %s" % paste_url)
			elif mparts[0] == "!help":
				logger.debug("!help called")
				self.msg(channel, "%s: Usage: !pb" % user)

	def alterCollidedNick(self, nickname):
		nickname = "%s-%d" (nickname, random.rand(0, 5))
		return nickname

	def who(self, channel):
		self.sendLine('WHO %s' % channel)
	
	def whois(self, user):
		self.sendLine('WHOIS %s' % user)

	# IRC callbacks
	def irc_PING(self, prefix, params):
		self.db.pong()
		self.sendLine("PONG %s" % params[-1])

	def irc_RPL_WHOISUSER(self, prefix, params):
		user = params[1]
		host = params[3]

		try:
			ip = socket.gethostbyname_ex(host)[2][0]
		except Exception, e:
			logger.info('Socket call failed: %s' % e)
			ip = host

		self.db.update_user(user, ip)

	def irc_RPL_WHOREPLY(self, prefix, params):
		user = params[5]
		host = params[3]

		try:
			ip = socket.gethostbyname_ex(host)[2][0]
		except Exception, e:
			logger.info('Socket call failed: %s' % e)
			ip = host

		self.db.add_user(user, ip)

	def userJoined(self, user, channel):
		self.db.add_user(user, '')
		self.whois(user)

	def userKicked(self, user, channel):
		self.userLeft(user, channel)

	def userQuit(self, user, channel):
		self.userLeft(user, channel)

	def userLeft(self, user, channel):
		self.db.remove_user(user)

	def userRenamed(self, old, new):
		self.db.rename_user(old, new)

	def modeChanged(self, user, channel, set, modes, args):
		user = user.split('!', 1)[0]
		if channel[0] == "#":
			# This is a bit lame and probably needs fixing
			if "v" in modes and self.nickname in args:
				if set == True:
					logger.info("Setting channel echo to true for %s" % channel)
					self.channels[channel] = True
				else:
					logger.info("Setting channel echo to false for %s" % channel)
					self.channels[channel] = False

class IRCBot(protocol.ClientFactory):
	protocol = IRCBotProtocol

	def __init__(self, channels, webnotify):
		self.channels = channels
		self.webnotify = webnotify

	def buildProtocol(self, addr):
		p = self.protocol(self.channels)
		p.factory = self
		return p

	def clientConnectionLost(self, connector, reason):
		logger.critical("Lost connection (%s)... trying reconnect" % reason)
		connector.connect()

	def clientConnectionFailed(self, connector, reason):
		logger.critical("Could not connect: %s" % reason)
		time.sleep(2)
		connector.connect()

if __name__ == '__main__':
	db = Database()
	db.clean()

	WebNotify = WebNotify()
	IRCBotFactory = IRCBot(IRC_CHANNELS, WebNotify)
	reactor.connectTCP(IRC_SERVER, IRC_PORT, IRCBotFactory)
	reactor.listenUDP(RC_PORT, WebNotify, interface=RC_IP)
	reactor.run()
