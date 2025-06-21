export function replaceSpecialChars(input: string): string {
  return input
    .replace(/ä/g, "æ")
    .replace(/Ä/g, "Æ")
    .replace(/ö/g, "ø")
    .replace(/Ö/g, "Ø")
    .replace(/ü/g, "å")
    .replace(/Ü/g, "Å");
}
