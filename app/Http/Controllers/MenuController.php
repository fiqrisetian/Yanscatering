<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Package;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->string('tab', 'menu')->toString();

        $menuQuery = Menu::query();
        if ($searchMenu = $request->string('qm')->trim()->toString()) {
            $menuQuery->where('name', 'like', "%{$searchMenu}%")
                      ->orWhere('category', 'like', "%{$searchMenu}%");
        }
        $menus = $menuQuery->orderBy('category')->orderBy('name')->get();

        $packageQuery = Package::query();
        if ($searchPkg = $request->string('qp')->trim()->toString()) {
            $packageQuery->where('name', 'like', "%{$searchPkg}%")
                         ->orWhere('description', 'like', "%{$searchPkg}%");
        }
        $packages = $packageQuery->latest('id')->get();

        $categories = Menu::distinct()->whereNotNull('category')->pluck('category');

        return view('menus.index', [
            'tab' => $tab,
            'menus' => $menus,
            'packages' => $packages,
            'categories' => $categories,
        ]);
    }

    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        Menu::create($validated);

        return redirect()->route('menus.index', ['tab' => 'menu'])
            ->with('success', "Menu '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $menu->update($validated);

        return redirect()->route('menus.index', ['tab' => 'menu'])
            ->with('success', "Menu '{$menu->name}' berhasil diperbarui.");
    }

    public function destroyMenu(Menu $menu)
    {
        $name = $menu->name;
        $menu->delete();

        return redirect()->route('menus.index', ['tab' => 'menu'])
            ->with('success', "Menu '{$name}' berhasil dihapus.");
    }

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        Package::create($validated);

        return redirect()->route('menus.index', ['tab' => 'package'])
            ->with('success', "Paket Catering '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updatePackage(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $package->update($validated);

        return redirect()->route('menus.index', ['tab' => 'package'])
            ->with('success', "Paket Catering '{$package->name}' berhasil diperbarui.");
    }

    public function destroyPackage(Package $package)
    {
        $name = $package->name;
        $package->delete();

        return redirect()->route('menus.index', ['tab' => 'package'])
            ->with('success', "Paket Catering '{$name}' berhasil dihapus.");
    }
}
