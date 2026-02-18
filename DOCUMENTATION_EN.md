# ActiveCampaign Plugin for SugarCRM - Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Data Synchronization](#data-synchronization)
4. [Synchronization Direction](#synchronization-direction)
5. [Limitations](#limitations)
6. [Requirements](#requirements)
7. [Configuration](#configuration)
8. [Plugin Usage](#plugin-usage)

---

## Overview

The **ActiveCampaign for SugarCRM** plugin enables bidirectional integration between SugarCRM and ActiveCampaign, facilitating data synchronization of contacts, accounts, leads, and target lists between both platforms, as well as tracking email marketing campaign statistics.

**Version:** 3.2  
**Compatibility:** SugarCRM 9.x  
**Required PHP:** 7.3

---

## Features

### 1. Data Synchronization
The plugin synchronizes the following SugarCRM modules with ActiveCampaign:

- **Contacts**
- **Accounts**
- **Leads**
- **Contracts**
- **Target Lists**

### 2. Campaign Statistics Tracking
Through webhooks, the plugin captures and records the following campaign activities:

- **Sent:** When a campaign email is sent
- **Opened:** When a contact opens the email
- **Clicked:** When a contact clicks on links in the email

### 3. Lead Scoring
- Synchronization of lead scores from ActiveCampaign to SugarCRM
- Automatic update of the `ac_lead_score_c` field in Contacts, Accounts, and Leads

### 4. Custom Fields
The plugin automatically creates custom fields in ActiveCampaign:
- **CRM_SOURCE:** Source module (Contacts, Accounts, Leads)
- **CRM_RECORD_ID:** Record ID in SugarCRM

### 5. Customizable Field Mapping
- Configuration interface to map SugarCRM fields with ActiveCampaign fields
- Support for custom fields in both systems

---

## Data Synchronization

### Standard Synchronized Fields

#### Contacts
- **Email** (required field)
- **First Name (first_name)**
- **Last Name (last_name)**
- **Mobile Phone (phone_mobile)**
- **ActiveCampaign ID (active_campaign_id_c)**
- **Sync Status (sync_status_c)**
- **Lead Score (ac_lead_score_c)**
- **Mapped custom fields**

#### Accounts
- **Email** (required field)
- **Name (name)**
- **Office Phone (phone_office)**
- **ActiveCampaign ID (active_campaign_id_c)**
- **Sync Status (sync_status_c)**
- **Lead Score (ac_lead_score_c)**
- **Mapped custom fields**

#### Leads
- **Email** (required field)
- **First Name (first_name)**
- **Last Name (last_name)**
- **Mobile Phone (phone_mobile)**
- **ActiveCampaign ID (active_campaign_id_c)**
- **Sync Status (sync_status_c)**
- **Lead Score (ac_lead_score_c)**
- **Mapped custom fields**

#### Contracts
- **Associated contact's email**
- **Mapped custom fields**

### Synchronized Campaign Statistics
Statistics are stored in the **TL_ActiveCampaigns** module with:
- **Campaign name**
- **Activity date**
- **ActiveCampaign campaign ID**
- **Status (sent/opened/clicked)**
- **Relationship with Contact/Account/Lead**

---

## Synchronization Direction

### From SugarCRM to ActiveCampaign

#### 1. New Records
**When:** When creating or saving a new record in SugarCRM  
**How:** Logic Hooks (after_save)  
**Conditions:**
- New record synchronization must be enabled in configuration
- Record must have a valid email (required field)
- Record must be associated with a Target List mapped to an ActiveCampaign list

**Affected modules:**
- Contacts → ActiveCampaign Contacts
- Accounts → ActiveCampaign Contacts
- Leads → ActiveCampaign Contacts
- Contracts → ActiveCampaign Contacts

#### 2. Updating Existing Records
**When:** When modifying an existing record in SugarCRM  
**How:** Logic Hooks (before_save/after_save)  
**Conditions:**
- At least 5 minutes must have elapsed since the last update
- Record must have a valid `active_campaign_id_c`
- Synchronization must be enabled

#### 3. Record Deletion
**When:** When deleting a record in SugarCRM  
**How:** Logic Hooks (after_delete)  
**Result:** The contact is deleted from ActiveCampaign

#### 4. Bulk Synchronization (Schedulers)
**When:** Via scheduled jobs  
**Frequency:** Every 15 minutes (configurable)  
**Functionality:**
- Synchronizes existing records that don't have `active_campaign_id_c` yet
- Processes up to 300 records per execution (3 batches of 100)

**Available schedulers:**
- `CreateCampaignContact` - Syncs existing contacts
- `CreateCampaignAccount` - Syncs existing accounts
- `CreateCampaignLead` - Syncs existing leads
- `CreateCampaignContract` - Syncs existing contracts
- `GetLeadScores` - Updates lead scores
- `GetExistingLeadScores` - Updates historical scores

### From ActiveCampaign to SugarCRM

#### 1. Webhooks for Campaign Statistics
**Endpoint:** `/rest/v11_4/TL_ActiveCampaigns/CampaignStats`  
**Captured events:**
- `sent` - Email sent
- `open` - Email opened
- `click` - Link clicked

**Process:**
1. ActiveCampaign sends webhook when an event occurs
2. Plugin searches for contact in SugarCRM using `CRM_RECORD_ID`
3. Creates a record in the `TL_ActiveCampaigns` module
4. Relates the record with Contact/Account/Lead via subpanel

#### 2. Contact Synchronization (Optional - Currently Disabled)
**Available endpoints (not currently used):**
- `/rest/v11_4/TL_ActiveCampaigns/SyncAcContacts` - Create contacts
- `/rest/v11_4/TL_ActiveCampaigns/UpdateAcContacts` - Update contacts

**Note:** These endpoints are implemented but corresponding webhooks are not automatically created.

#### 3. Lead Scoring
**When:** Via configured scheduler  
**Direction:** ActiveCampaign → SugarCRM  
**Functionality:**
- Gets contacts updated in the last 2 days
- Synchronizes `scoreValue` field to `ac_lead_score_c` field
- Updates Contacts, Accounts, and Leads

---

## Limitations

### Technical Limitations

#### 1. Email Requirement
**Description:** All records must have a valid email address to synchronize.  
**Impact:** Records without email cannot sync to ActiveCampaign.  
**Affected modules:** All (Contacts, Accounts, Leads, Contracts)

#### 2. Unidirectional Master Data Synchronization
**Description:** Master data synchronization (name, last name, phone) is primarily from SugarCRM to ActiveCampaign.  
**Impact:** Changes to these fields in ActiveCampaign are not automatically reflected in SugarCRM (except via unconfigured endpoints).

#### 3. Update Throttling
**Description:** Updates to the same record are only processed if more than 5 minutes have passed since the last modification.  
**Impact:** Rapid successive changes are not all synchronized.  
**Reason:** Avoid API overload and duplicate updates.

#### 4. Scheduler Limitations
**Description:** 
- Process maximum 300 records per execution (3 batches of 100)
- Execute every 15 minutes by default
  
**Impact:** Bulk synchronization of large data volumes can take several hours.

#### 5. Dependency on Target Lists for Automatic Synchronization
**Description:** For a new record to sync automatically, it must be associated with a Target List mapped to an ActiveCampaign list.  
**Impact:** Records created directly without Target List require:
- Global mapping of ActiveCampaign lists for the module, OR
- Later synchronization via scheduler

#### 6. Lead Scoring - Limited Synchronization
**Description:** 
- Only synchronizes contacts updated in the last 2 days
- Historical scores scheduler automatically deactivates after 50 executions without data
  
**Impact:** Old scores may not fully synchronize.

### ActiveCampaign API Limitations

#### 7. Rate Limiting
**Description:** ActiveCampaign imposes rate limits on its API.  
**Impact:** Very large bulk synchronizations may fail or slow down.  
**Mitigation:** Schedulers process in small batches (100 records).

#### 8. Webhooks Require Public URL
**Description:** ActiveCampaign webhooks need a publicly accessible URL.  
**Impact:** Does not work in localhost environments without tunnel (e.g., ngrok).  
**Solution:** Plugin detects ngrok and localhost URLs and adjusts webhook URL.

### License Limitations

#### 9. License Validation Required
**Description:** Plugin requires a valid SugarActive Outfitters license.  
**Impact:** Without valid license, functionalities do not execute.  
**Validation:** Validated on every major operation (sync, webhooks, config).

### Functional Limitations

#### 10. No Deletion Sync from ActiveCampaign
**Description:** If a contact is deleted in ActiveCampaign, it's not automatically deleted in SugarCRM.  
**Direction:** Only SugarCRM → ActiveCampaign for deletions.

#### 11. Campaign Statistics Read-Only
**Description:** Statistics created in TL_ActiveCampaigns module cannot be meaningfully edited manually.  
**Impact:** They are for viewing and reporting only.

#### 12. Single Statistics Record per Campaign
**Description:** If a statistic already exists for a specific campaign and contact, status is updated instead of creating a new record.  
**Impact:** No history of multiple interactions with the same campaign.

#### 13. Manual Field Mapping
**Description:** Custom fields must be manually mapped in the configuration interface.  
**Impact:** Requires initial configuration and maintenance when adding new fields.

#### 14. Email-Based Duplicates
**Description:** Duplicate detection is primarily based on email.  
**Impact:** Contacts with different emails but similar data may duplicate.

#### 15. Limited Contract Synchronization
**Description:** Contracts sync using the associated contact's email, not contract-specific data.  
**Impact:** Contract functionality is limited compared to other modules.

### Performance Considerations

#### 16. Synchronous Processing in Logic Hooks
**Description:** Logic Hooks execute API calls synchronously.  
**Impact:** 
- Saving a record may take longer (waits for ActiveCampaign response)
- If ActiveCampaign is slow, it affects user experience

#### 17. Extensive Logging
**Description:** Plugin generates detailed logs of all operations.  
**Impact:** 
- Log files can grow quickly
- Useful for debugging but requires periodic cleanup
  
**Mitigation:** Interface to download and clear logs.

---

## Requirements

### Technical Requirements
- **SugarCRM:** Version 9.x
- **PHP:** Version 7.3
- **Database:** MySQL/MariaDB with permissions to create tables
- **Web Server:** Publicly accessible (for webhooks)

### ActiveCampaign Requirements
- Active **ActiveCampaign account**
- **API Key** with full permissions
- Account **API URL**

### License Requirements
- Valid **SugarActive Outfitters license** for TL_ActiveCampaigns

---

## Configuration

### 1. Installation
1. Access SugarCRM administration panel
2. Go to **Module Loader**
3. Upload the plugin ZIP file
4. Execute installation
5. Perform **Repair & Rebuild** from Admin → Repair

### 2. Initial Configuration

#### Step 1: ActiveCampaign Authentication
1. Navigate to **TL_ActiveCampaigns** → **Config**
2. Enter:
   - **API URL:** ActiveCampaign API URL (e.g., `https://YOURACCOUNT.api-us1.com`)
   - **API Key:** ActiveCampaign API Key
3. Click **Check API** to verify connection
4. Save configuration

**Note:** Upon saving, the plugin:
- Creates `tl_migrated_stats` table for tracking
- Creates webhooks in ActiveCampaign automatically
- Creates custom fields in ActiveCampaign (CRM_SOURCE, CRM_RECORD_ID)

#### Step 2: Map Lists
1. On configuration page, select:
   - **SugarCRM Target List**
   - Corresponding **ActiveCampaign List(s)**
2. Enable synchronization options:
   - **Sync Existing:** Synchronize existing records
   - **Sync New:** Automatically synchronize new records
3. Save configuration

#### Step 3: Map Fields
1. Navigate to **TL_ActiveCampaigns** → **Mapping**
2. For each module (Contacts, Accounts, Leads, Contracts):
   - Select SugarCRM field
   - Select corresponding ActiveCampaign field
   - Add to mapping
3. Save mapping

**Required fields:**
- Email (always required)
- First Name
- Last Name

#### Step 4: Activate Schedulers (Optional)
1. Go to **Admin** → **Schedulers**
2. Find ActiveCampaign schedulers:
   - ActiveCampaigns - Sync Existing Contacts
   - ActiveCampaigns - Sync Existing Accounts
   - ActiveCampaigns - Sync Existing Leads
   - ActiveCampaigns - Sync Existing Contracts
   - ActiveCampaigns - Get Lead Scores
3. Configure frequency (default: every 15 minutes)
4. Activate as needed

---

## Plugin Usage

### Synchronize New Records
1. Create or edit a Contact/Account/Lead
2. Add to a Target List mapped with ActiveCampaign
3. Record syncs automatically if "Sync New" is enabled
4. Verify `active_campaign_id_c` field is populated with ActiveCampaign ID

### Synchronize Existing Records
**Option 1: Manual via Target Lists**
1. Add records to a mapped Target List
2. Ensure "Sync Existing" is enabled for that list
3. Scheduler will synchronize the records

**Option 2: Automatic Scheduler**
1. Activate corresponding scheduler for the module
2. Scheduler will process records without `active_campaign_id_c`
3. Maximum 300 records per execution

### View Campaign Statistics
1. Open a Contact/Account/Lead
2. Go to **ActiveCampaigns** subpanel
3. View campaign statistics:
   - Campaign name
   - Activity date
   - Status (Sent/Opened/Clicked)

### Verify Logs
1. Navigate to **TL_ActiveCampaigns** → **Logs**
2. Review operations and errors
3. Download or clear logs as needed

### Monitor Lead Scores
1. `ac_lead_score_c` fields update automatically
2. Available in:
   - Contact/Account/Lead DetailView
   - ListView (if added to layout)
   - Reports and dashboards

---

## Common Troubleshooting

### Records not synchronizing
**Check:**
- [ ] Email is valid and not empty
- [ ] License is valid
- [ ] API Key and URL are correct
- [ ] Target List is mapped
- [ ] "Sync New" or "Sync Existing" is enabled
- [ ] Review logs for specific errors

### Webhooks not receiving data
**Check:**
- [ ] Server URL is publicly accessible
- [ ] Webhook was created in ActiveCampaign (check in AC)
- [ ] Firewall not blocking ActiveCampaign requests
- [ ] URL contains correct protocol (https preferred)

### Schedulers not executing
**Check:**
- [ ] Schedulers are in "Active" status
- [ ] SugarCRM cron is running
- [ ] No license errors
- [ ] Review SugarCRM scheduler logs

### Updates not reflecting
**Check:**
- [ ] More than 5 minutes have passed since last update
- [ ] `active_campaign_id_c` field has a valid value
- [ ] Synchronization is enabled in configuration

---

## Support and Contact

For assistance with installation or plugin usage, contact the plugin provider.

**Reference Documentation:**
- [SugarCRM Developer Guide](https://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_9.0)
- [ActiveCampaign API Documentation](https://www.activecampaign.com/api/overview.php)

---

## Changelog

### Version 3.2
- Bidirectional synchronization of Contacts, Accounts, Leads, Contracts
- Webhooks for campaign statistics (sent/opened/clicked)
- Automatic lead scoring
- Customizable field mapping
- Schedulers for bulk synchronization
- Improved configuration interface
- Detailed logging system

---

**Last updated:** 2026-02-18
