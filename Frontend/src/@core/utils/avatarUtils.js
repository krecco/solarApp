export const getInitials = str => {
  const names = str.trim().split(' ')
  let initials = names[0].substring(0, 1).toUpperCase()

  if (names.length > 1) {
    initials += names[names.length - 1].substring(0, 1).toUpperCase()
  }
  return initials
}

export const getStyle = str => {
  let hash = 0
  if (str.length === 0) return hash
  for (let i = 0; i < str.length; i += 1) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash) //  eslint-disable-line no-bitwise
    hash &= hash //  eslint-disable-line no-bitwise
  }
  let color = '#'
  let value = 0
  for (let i = 0; i < 3; i += 1) {
    value = (hash >> (i * 8)) & 255 //  eslint-disable-line no-bitwise
    color += (`00${value.toString(16)}`).substr(-2)
  }
  return `background-color:${color}`
}
