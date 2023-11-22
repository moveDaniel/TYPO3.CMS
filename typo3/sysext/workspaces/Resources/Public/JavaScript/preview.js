/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import{SeverityEnum}from"@typo3/backend/enum/severity.js";import DocumentService from"@typo3/core/document-service.js";import Modal from"@typo3/backend/modal.js";import Utility from"@typo3/backend/utility.js";import Workspaces from"@typo3/workspaces/workspaces.js";import ThrottleEvent from"@typo3/core/event/throttle-event.js";import"@typo3/workspaces/renderable/send-to-stage-form.js";import RegularEvent from"@typo3/core/event/regular-event.js";var Identifiers;!function(e){e.topbar="#typo3-topbar",e.workspacePanel=".workspace-panel",e.liveView="#live-view",e.stageSlider="#workspace-stage-slider",e.workspaceView="#workspace-view",e.sendToStageAction='[data-action="send-to-stage"]',e.discardAction='[data-action="discard"]',e.stageButtonsContainer=".t3js-stage-buttons",e.previewModeContainer=".t3js-preview-mode",e.activePreviewMode=".t3js-active-preview-mode",e.workspacePreview=".t3js-workspace-preview"}(Identifiers||(Identifiers={}));class Preview extends Workspaces{constructor(){super(),this.currentSlidePosition=100,this.elements={},DocumentService.ready().then((()=>{this.getElements(),this.resizeViews(),this.registerEvents()}))}static getAvailableSpace(){return document.documentElement.clientHeight-document.querySelector(Identifiers.topbar).offsetHeight}getElements(){this.elements.liveView=document.querySelector(Identifiers.liveView),this.elements.workspacePanel=document.querySelector(Identifiers.workspacePanel),this.elements.stageSlider=document.querySelector(Identifiers.stageSlider),this.elements.workspaceView=document.querySelector(Identifiers.workspaceView),this.elements.stageButtonsContainer=document.querySelector(Identifiers.stageButtonsContainer),this.elements.previewModeContainer=document.querySelector(Identifiers.previewModeContainer),this.elements.activePreviewMode=document.querySelector(Identifiers.activePreviewMode),this.elements.workspacePreview=document.querySelector(Identifiers.workspacePreview)}registerEvents(){new ThrottleEvent("resize",(()=>{this.resizeViews()}),50).bindTo(window),new RegularEvent("click",this.renderDiscardWindow.bind(this)).delegateTo(document,Identifiers.discardAction),new RegularEvent("click",this.renderSendPageToStageWindow.bind(this)).delegateTo(document,Identifiers.sendToStageAction),new RegularEvent("click",(()=>{window.top.document.querySelectorAll(".t3js-workspace-recipient:not(:disabled)").forEach((e=>{e.checked=!0}))})).delegateTo(document,".t3js-workspace-recipients-selectall"),new RegularEvent("click",(()=>{window.top.document.querySelectorAll(".t3js-workspace-recipient:not(:disabled)").forEach((e=>{e.checked=!1}))})).delegateTo(document,".t3js-workspace-recipients-deselectall"),new ThrottleEvent("input",this.updateSlidePosition.bind(this),10).bindTo(document.querySelector(Identifiers.stageSlider)),new RegularEvent("click",this.changePreviewMode.bind(this)).delegateTo(this.elements.previewModeContainer,"[data-preview-mode]")}renderStageButtons(e){this.elements.stageButtonsContainer.innerHTML=e}updateSlidePosition(e){this.currentSlidePosition=parseInt(e.target.value,10),this.resizeViews()}resizeViews(){const e=Preview.getAvailableSpace(),t=-1*(this.currentSlidePosition-100),i=Math.round(Math.abs(e*t/100)),s=this.elements.liveView.offsetHeight-this.elements.liveView.clientHeight;this.elements.workspacePreview.style.height=e+"px","slider"===this.elements.activePreviewMode.dataset.activePreviewMode&&(this.elements.liveView.style.height=i-s+"px")}renderDiscardWindow(){const e=Modal.confirm(TYPO3.lang["window.discardAll.title"],TYPO3.lang["window.discardAll.message"],SeverityEnum.warning,[{text:TYPO3.lang.cancel,active:!0,btnClass:"btn-default",name:"cancel",trigger:()=>{e.hideModal()}},{text:TYPO3.lang.ok,btnClass:"btn-warning",name:"ok"}]);e.addEventListener("button.clicked",(t=>{"ok"===t.target.name&&this.sendRemoteRequest([this.generateRemoteActionsPayload("discardStagesFromPage",[TYPO3.settings.Workspaces.id]),this.generateRemoteActionsPayload("updateStageChangeButtons",[TYPO3.settings.Workspaces.id])],"#typo3-topbar").then((async t=>{e.hideModal(),this.renderStageButtons((await t.resolve())[1].result),this.elements.workspaceView.setAttribute("src",this.elements.workspaceView.getAttribute("src"))}))}))}renderSendPageToStageWindow(e,t){const i=t.dataset.direction;let s;if("prev"===i)s="sendPageToPreviousStage";else{if("next"!==i)throw"Invalid direction "+i+" requested.";s="sendPageToNextStage"}this.sendRemoteRequest(this.generateRemoteActionsPayload(s,[TYPO3.settings.Workspaces.id]),"#typo3-topbar").then((async e=>{const i=await e.resolve(),s=this.renderSendToStageWindow(i);s.addEventListener("button.clicked",(e=>{if("ok"===e.target.name){const e=Utility.convertFormToObject(s.querySelector("form"));e.affects=i[0].result.affects,e.stageId=parseInt(t.dataset.stageId,10),this.sendRemoteRequest([this.generateRemoteActionsPayload("sentCollectionToStage",[e]),this.generateRemoteActionsPayload("updateStageChangeButtons",[TYPO3.settings.Workspaces.id])],"#typo3-topbar").then((async e=>{s.hideModal(),this.renderStageButtons((await e.resolve())[1].result)}))}}))}))}changePreviewMode(e,t){e.preventDefault();const i=this.elements.activePreviewMode.dataset.activePreviewMode,s=t.dataset.previewMode;this.elements.activePreviewMode.textContent=t.textContent,this.elements.activePreviewMode.dataset.activePreviewMode=s,this.elements.workspacePreview.parentElement.classList.remove("preview-mode-"+i),this.elements.workspacePreview.parentElement.classList.add("preview-mode-"+s),"slider"===s?(this.elements.stageSlider.parentElement.style.display="",this.resizeViews()):(this.elements.stageSlider.parentElement.style.display="none",this.elements.liveView.style.height="vbox"===s?"100%":"50%")}}export default new Preview;