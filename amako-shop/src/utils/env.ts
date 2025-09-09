export const isExpoGo =
  // @ts-ignore
  typeof global !== 'undefined' && !!(global as any).ExpoGo;
