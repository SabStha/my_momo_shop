import os
import re

def scan_routes(route_file):
    with open(route_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Match patterns like [HomeController::class, 'index'] or 'HomeController@index'
    # Pattern 1: [ControllerName::class, 'methodName']
    class_pattern = r'\[\s*([a-zA-Z0-9_\\]+)::class\s*,\s*\'([a-zA-Z0-9_]+)\'\s*\]'
    # Pattern 2: 'ControllerName@methodName'
    string_pattern = r'[\"\']([a-zA-Z0-9_\\\\]+)@([a-zA-Z0-9_]+)[\"\']'

    matches = re.findall(class_pattern, content)
    matches += re.findall(string_pattern, content)

    # Track imports to resolve class names
    imports = re.findall(r'use ([\w\\]+);', content)
    import_map = {imp.split('\\')[-1]: imp for imp in imports}

    broken_routes = []

    for controller, method in matches:
        # Resolve full class path
        full_path = import_map.get(controller, controller)
        if not full_path.startswith('App\\'):
            # Try to guess if it's in App\Http\Controllers
            if not full_path.startswith('\\'):
                full_path = 'App\\Http\\Controllers\\' + full_path

        # Convert namespace to file path
        rel_path = full_path.replace('\\', '/') + '.php'
        abs_path = os.path.join('c:/Users/user/my_momo_shop/app', rel_path.replace('App/', ''))
        
        # Correct path if it's double Controllers
        if not os.path.exists(abs_path):
             abs_path = os.path.join('c:/Users/user/my_momo_shop/app/Http/Controllers', controller + '.php')

        if not os.path.exists(abs_path):
            # Try subdirectories
            found = False
            for root, dirs, files in os.walk('c:/Users/user/my_momo_shop/app/Http/Controllers'):
                if controller + '.php' in files:
                    abs_path = os.path.join(root, controller + '.php')
                    found = True
                    break
            if not found:
                broken_routes.append(f"MISSING_CONTROLLER: {controller} (method {method})")
                continue

        with open(abs_path, 'r', encoding='utf-8', errors='ignore') as f:
            file_content = f.read()
            if f'function {method}' not in file_content:
                broken_routes.append(f"MISSING_METHOD: {controller}@{method} in {abs_path}")

    return broken_routes

web_broken = scan_routes('c:/Users/user/my_momo_shop/routes/web.php')
api_broken = scan_routes('c:/Users/user/my_momo_shop/routes/api.php')

print("--- WEB BROKEN ROUTES ---")
for r in set(web_broken): print(r)
print("\n--- API BROKEN ROUTES ---")
for r in set(api_broken): print(r)
