# Arup Online Appraisal system

## Intro

The Arup online appraisal system is a form based tool to track a workers progress in their career by asking questions and providing tools to plan learning and development and promote feedback on a users progress.

Users enter the system from the Moodle navigation bar and are put into costcentres using the local_costcentre plugin.

There are different user types setup in the costcentre plugin that perform different actions in this tool. The standard user is an appraisee. Each appraisee should have an appraiser and a group leader/sign off to complete an appraisal.

This tool is based on the appraisal system build by Leo. It has been developed further by Simon Lewis and Bas Brands.

## NEW Structure

* The UI bits: /templates, renderer.php
* User entry point: view.php
* Logic: locallib.php
* Default Moodle folders: pix, db, lang

All other files and folders are part of the legacy code.

