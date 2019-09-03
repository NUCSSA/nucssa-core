export function httpizeLink(link, secure = false) {
  if (link.startsWith('http')) return link;

  return secure ? `https://${link}` : `http://${link}`;
}