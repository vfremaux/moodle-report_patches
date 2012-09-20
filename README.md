Versions
========

Moodle 2.X as master branch

Moodle 1.9 as MOODLE_19_STABLE branch

moodle-report_patches
=====================

A report that scans core patches and help control migration of patched codebases.

Principle
=========

Pursuant all patchs are integrated with consistant start and end range markers, the Core Patch Report
provides a code scanner that inspect all Moodle source and searches for start/end pairs so the changes
in core will be indexed and identified.

The start marker is a regular expression that will provide a substring as output as "rationale" for the
patch, usually the description of the feature it serves.

The report stores the result of the scan in a patch index table so it can be consulted often without 
launching full code scan all the time.

Patch list result can be sorted by "source file", or by "reason" so giving a view of all changes in
the same file, or conversely all changes needed by a single feazture, pursuant the marking is consistant
for this feature.

Location and installation
=========================

Installs very simlply by dropping the report directory in : 

&lt;moodleroot&gt;/admin/report