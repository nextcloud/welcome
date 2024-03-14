# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 1.1.0 – 2024-03-14
### Changed
- Update node pkgs (mainly nc/vue8) @kyteinsky

### Fixed
- add missing config property in OCA\Welcome\AppInfo\Application @tcitworld
- use NcSelect instead of the NcMultiselect vue component @kyteinsky
- fix file picker on nc/vue8 and style fixes @kyteinsky

### Added
- add button in settings to enable the widget for all active users @julien-nc
- composer setup and gh workflow addition @kyteinsky

## 1.0.10 – 2023-09-17
### Changed
- bump max NC version to 28 @julien-nc
- add PR feedback GH action @julien-nc

### Fixed
- update deprecated query of config class @kyteinsky
- build file picker with @nextcloud/dialogs @julien-nc

## 1.0.9 – 2023-04-26
### Changed
- update npm pkgs, use NcRichText from `@nextcloud/vue`, remove `@nextcloud/vue-richtext` @julien-nc

### Fixed
- safer check that markdown file exists @julien-nc

## 1.0.8 – 2023-02-06
### Fixed
- safer FileService::getWidgetHttpImageUrls

## 1.0.6 – 2022-12-28
### Fixed
- Use a Welcome endpoint to get images from file ID. Images are only returned if they are in the markdown file's attachment folder
- bug when decoded and encoded image path (in image link) are different

## 1.0.4 – 2022-12-25
### Fixed
- fix compat with php < 8

## 1.0.3 – 2022-12-24
### Added
- support for http markdown image target

### Changed
- update npm pkgs

### Fixed
- support for image links inserted by Text

## 1.0.2 – 2022-10-08
### Changed
- use @nextcloud/vue-richtext instead of vue-markdown
- bump js libs
- make things work and look nice on NC 25/26

## 1.0.1 – 2021-11-18
### Added
- now possible to set the widget title
  [#19](https://github.com/julien-nc/welcome/issues/19) @42ske
- tooltip on support contact call link

### Changed
- clarify package.json
- get rid of deprecated stuff in server side code
- bump min NC version to 22

## 1.0.0 – 2021-08-30
### Changed
- improve markdown rendering style for <p>, <a> and <ul>
- update dependencies

## 0.0.6 – 2021-08-23
### Changed
- bump js libs
- bump max NC version to 23
- improve release action

### Fixed
- style of paragraphs and nested lists
[#17](https://github.com/julien-nc/welcome/issues/17) @RussellNS

## 0.0.5 – 2021-02-18
### Changed
- image size
- section headings style

## 0.0.3 – 2021-02-16
### Added
- appstore release

## 0.0.2 – 2021-02-02
### Changed
- README, app description, screenshot

## 0.0.1 – 2021-01-23
### Added
* the app
