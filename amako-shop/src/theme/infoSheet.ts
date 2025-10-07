// FoodInfoSheet theme constants and helpers

export const infoSheetColors = {
  ingredients: '#2ecc71', // Green
  allergen: '#f39c12',    // Amber
  nutrition: '#e67e22',   // Orange
  dietary: '#3498db',     // Blue
} as const;

export const infoSheetIcons = {
  ingredients: 'leaf',
  allergen: 'alert-circle',
  nutrition: 'fire',
  dietary: 'food',
} as const;

export const infoSheetDimensions = {
  headerHeight: 200,
  maxHeight: '90%',
  borderRadius: 18,
  cardBorderRadius: 12,
  cardPadding: 12,
  bodyPadding: 16,
} as const;

export const infoSheetTypography = {
  cardTitle: {
    fontWeight: '700' as const,
    marginBottom: 4,
  },
  cardBody: {
    color: '#555',
    fontSize: 14,
    lineHeight: 20,
  },
  closeButton: {
    fontSize: 16,
    fontWeight: '600' as const,
  },
} as const;
