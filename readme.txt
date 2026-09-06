=== BahriCanli Connect ===
Contributors: bmericc
Tags: whatsapp, business messaging, inbox, customer support, crm
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WhatsApp Business team inbox inside WordPress. Connects to the Message Manager platform to read and reply to customer conversations.

== Description ==

Bahri Canli Connect lets a business manage its own WhatsApp Business account from
the WordPress admin area, through the Message Manager platform
(https://message-manager.tr):

* Shared team inbox — incoming and outgoing WhatsApp messages
* Reply to customer conversations without leaving WordPress
* 24-hour customer service window awareness

The plugin is a thin client. It stores no message data of its own. All business
logic and the connection to the Meta WhatsApp Business Platform (Cloud API) run on
the Message Manager service. The plugin only talks to that service from your
WordPress server, using a per-account API key. The API key is never exposed to the
browser.

== External services ==

This plugin connects to one external service: the **Message Manager API** at
`https://message-manager.tr`. A connection is made only after you enter an API key
on the plugin settings screen, and only while a logged-in administrator is using
the plugin's screens (Settings, Inbox).

What is sent to Message Manager:

* Your API key, in the `Authorization` request header, to authenticate the request.
* When you open a conversation: the conversation identifier you selected.
* When you send a reply: the conversation identifier and the message text you typed.

What is received from Message Manager and shown in wp-admin:

* The list of your WhatsApp conversations (contact name/number, status, unread count).
* The messages of a conversation you open (text, direction, delivery status, timestamps).

Message Manager, in turn, communicates with the Meta WhatsApp Business Platform
(Cloud API) on your behalf to deliver and receive those messages. No data is sent
to any other third party. Message Manager does not sell or share your data.

* Message Manager website: https://message-manager.tr
* Message Manager Terms of Service: https://message-manager.tr/terms-of-service
* Message Manager Privacy Policy: https://message-manager.tr/privacy-policy
* Integration details: https://message-manager.tr/wordpress-plugin
* Meta WhatsApp Business Platform Terms: https://www.whatsapp.com/legal/business-policy

== Installation ==

1. Upload the plugin to `/wp-content/plugins/bahricanli-connect` and activate it.
2. Go to **Connect > Settings** and enter the API key from your Message Manager
   account, then click **Test connection**.
3. Open **Connect > Inbox** to read and reply to WhatsApp conversations.

== Frequently Asked Questions ==

= Where do I get an API key? =

From the Message Manager panel: open your tenant, go to **API Keys**, and create
one. The full key is shown only once.

= Is my API key safe? =

Yes. It is stored only in your WordPress database (`options`), never sent to the
browser, and only its SHA-256 hash is kept on the Message Manager side. You can
revoke it at any time from the Message Manager panel.

= Does the plugin store WhatsApp messages in WordPress? =

No. Messages are fetched from the Message Manager API on demand and are not written
to your WordPress database.

== Changelog ==

= 0.1.1 =
* Unique `bahrco`/`BAHRCO` prefix for all class names and AJAX actions.
* Removed the unnecessary `load_plugin_textdomain()` call.

= 0.1.0 =
* Initial release: settings, connection test, inbox (list + reply).
