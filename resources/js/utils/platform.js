export const isMacintosh = window.navigator.userAgentData.platform.indexOf("Mac") > -1;

export const searchKeyboardShortcutMainKey = isMacintosh ? "⌘" : "Ctrl";

export const searchKeyboardShortcutKey = "K";

export const shouldUseSearchKeyboardShortcut = !(
  /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.userAgent) ||
  /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.platform)
);