#!/usr/bin/env python3
"""
Feature Graphic Generator for AmaKo Momo Shop
Generates a 1024x500 feature graphic for Google Play Store

Requirements:
    pip install pillow

Usage:
    python generate_feature_graphic.py
"""

try:
    from PIL import Image, ImageDraw, ImageFont
    import os
except ImportError:
    print("❌ Pillow library not found!")
    print("📦 Please install it with: pip install pillow")
    exit(1)

# Configuration
WIDTH = 1024
HEIGHT = 500
OUTPUT_FILE = "amako-feature-graphic.png"

# Colors (Your brand colors)
ORANGE_PRIMARY = "#FF6B35"
ORANGE_LIGHT = "#FF8C42"
ORANGE_GOLD = "#FFA500"
WHITE = "#FFFFFF"
DARK_BLUE = "#152039"

def hex_to_rgb(hex_color):
    """Convert hex color to RGB tuple"""
    hex_color = hex_color.lstrip('#')
    return tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))

def create_gradient(width, height, color1, color2):
    """Create a gradient image"""
    base = Image.new('RGB', (width, height), color1)
    top = Image.new('RGB', (width, height), color2)
    mask = Image.new('L', (width, height))
    mask_data = []
    for y in range(height):
        for x in range(width):
            mask_data.append(int(255 * (x / width)))
    mask.putdata(mask_data)
    base.paste(top, (0, 0), mask)
    return base

def add_text_with_shadow(draw, text, position, font, fill_color, shadow_color=(0, 0, 0, 100)):
    """Add text with shadow effect"""
    x, y = position
    # Shadow
    draw.text((x + 3, y + 3), text, font=font, fill=shadow_color)
    # Main text
    draw.text((x, y), text, font=font, fill=fill_color)

def generate_feature_graphic():
    """Generate the feature graphic"""
    
    print("🎨 Generating feature graphic for AmaKo Momo Shop...")
    print(f"📐 Size: {WIDTH}×{HEIGHT} pixels")
    
    # Create gradient background
    print("🌈 Creating gradient background...")
    color1 = hex_to_rgb(ORANGE_PRIMARY)
    color2 = hex_to_rgb(ORANGE_GOLD)
    img = create_gradient(WIDTH, HEIGHT, color1, color2)
    
    # Create drawing context
    draw = ImageDraw.Draw(img, 'RGBA')
    
    # Add decorative circles
    print("✨ Adding decorative elements...")
    circle_color = (255, 255, 255, 30)  # Semi-transparent white
    draw.ellipse([50, 50, 200, 200], fill=circle_color)
    draw.ellipse([800, 300, 900, 400], fill=circle_color)
    draw.ellipse([900, 100, 980, 180], fill=circle_color)
    
    # Try to load fonts (with fallback)
    try:
        # Try to use system fonts (Windows)
        title_font = ImageFont.truetype("arial.ttf", 72)
        tagline_font = ImageFont.truetype("arial.ttf", 36)
        badge_font = ImageFont.truetype("arial.ttf", 20)
    except:
        try:
            # Try alternative font location
            title_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 72)
            tagline_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 36)
            badge_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 20)
        except:
            print("⚠️  Could not load custom fonts, using default...")
            title_font = ImageFont.load_default()
            tagline_font = ImageFont.load_default()
            badge_font = ImageFont.load_default()
    
    # Add main text
    print("📝 Adding text elements...")
    white_rgb = hex_to_rgb(WHITE)
    
    # App name (split into two lines for better layout)
    add_text_with_shadow(draw, "AmaKo", (60, 120), title_font, white_rgb)
    add_text_with_shadow(draw, "Momo Shop", (60, 200), title_font, white_rgb)
    
    # Tagline
    add_text_with_shadow(draw, "Fresh Momos, Fast Delivery! 🥟", (60, 300), tagline_font, white_rgb)
    
    # Feature badges (with background)
    print("🏷️  Adding feature badges...")
    badge_bg_color = (255, 255, 255, 60)
    
    # Badge 1: Real-Time Tracking
    draw.rounded_rectangle([60, 380, 280, 430], radius=25, fill=badge_bg_color)
    draw.text((80, 390), "📍 Real-Time Tracking", font=badge_font, fill=white_rgb)
    
    # Badge 2: Loyalty Rewards
    draw.rounded_rectangle([300, 380, 480, 430], radius=25, fill=badge_bg_color)
    draw.text((320, 390), "🎁 Loyalty Rewards", font=badge_font, fill=white_rgb)
    
    # Add mascot area (placeholder circle)
    print("👋 Adding mascot area...")
    mascot_circle_bg = (255, 255, 255, 50)
    mascot_x = 750
    mascot_y = 100
    mascot_size = 300
    
    # Outer glow circle
    draw.ellipse([mascot_x-10, mascot_y-10, mascot_x+mascot_size+10, mascot_y+mascot_size+10], 
                 fill=(255, 255, 255, 30))
    
    # Main circle
    draw.ellipse([mascot_x, mascot_y, mascot_x+mascot_size, mascot_y+mascot_size], 
                 fill=mascot_circle_bg, 
                 outline=(255, 255, 255, 100), 
                 width=5)
    
    # Mascot placeholder emoji (large)
    try:
        emoji_font = ImageFont.truetype("seguiemj.ttf", 150)  # Windows emoji font
        draw.text((mascot_x+75, mascot_y+75), "👋", font=emoji_font, fill=white_rgb)
    except:
        # Fallback text if emoji font not available
        mascot_text_font = ImageFont.truetype("arial.ttf", 48) if title_font != ImageFont.load_default() else ImageFont.load_default()
        draw.text((mascot_x+80, mascot_y+120), "MASCOT", font=mascot_text_font, fill=white_rgb)
        draw.text((mascot_x+80, mascot_y+170), "HERE", font=mascot_text_font, fill=white_rgb)
    
    # Save the image
    print(f"💾 Saving to {OUTPUT_FILE}...")
    img.save(OUTPUT_FILE, 'PNG', quality=100)
    
    # Verify dimensions
    saved_img = Image.open(OUTPUT_FILE)
    width, height = saved_img.size
    
    print("\n✅ Feature graphic generated successfully!")
    print(f"📁 File: {OUTPUT_FILE}")
    print(f"📐 Size: {width}×{height} pixels")
    print(f"💾 File size: {os.path.getsize(OUTPUT_FILE) / 1024:.1f} KB")
    
    if width == WIDTH and height == HEIGHT:
        print("✅ Dimensions are correct!")
    else:
        print(f"⚠️  Warning: Expected {WIDTH}×{HEIGHT}, got {width}×{height}")
    
    print("\n📋 Next steps:")
    print("1. Open the generated image")
    print("2. (Optional) Replace the mascot placeholder with your actual mascot image using an image editor")
    print("3. Upload to Google Play Console")
    print("\n🎉 Done!")

if __name__ == "__main__":
    try:
        generate_feature_graphic()
    except Exception as e:
        print(f"❌ Error: {e}")
        print("\n💡 Tip: Make sure Pillow is installed: pip install pillow")


