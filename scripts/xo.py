import sys
import random
import os

sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '../scripts')))

from tic_tac_toe import TicTacToe

def run_game(difficulty: str):
    game = TicTacToe()
    current_player = 'human' if random.random() < 0.5 else 'ai'

    while not game.is_game_over():
        if current_player == 'human':
            available = game.available_moves()
            if not available:
                break
            move = random.choice(available)
            game.make_move(move, game.human)
            if game.check_winner(game.human):
                return 'human'
            current_player = 'ai'
        else:
            if not game.available_moves():
                break
            move = game.get_best_move(difficulty)
            if move == -1:
                break
            game.make_move(move, game.ai)
            if game.check_winner(game.ai):
                return 'ai'
            current_player = 'human'

    return 'draw'


def run_tests(games_per_difficulty=50):
    results = {
        'easy': {'human': 0, 'ai': 0, 'draw': 0},
        'hard': {'human': 0, 'ai': 0, 'draw': 0}
    }

    total_games = games_per_difficulty * 2
    completed_games = 0

    for difficulty in ['easy', 'hard']:
        for _ in range(games_per_difficulty):
            winner = run_game(difficulty)
            results[difficulty][winner] += 1
            completed_games += 1
            print_progress_bar(completed_games, total_games)

    return results


def print_progress_bar(current, total, bar_length=30):
    percent = float(current) / total
    arrow = '-' * int(round(percent * bar_length) - 1) + '>'
    spaces = ' ' * (bar_length - len(arrow))

    sys.stdout.write(f'\rProgress: [{arrow + spaces}] {int(percent * 100)}%')
    sys.stdout.flush()

    if current == total:
        print()


def print_results(results):
    for difficulty, outcome in results.items():
        print(f"\nTic Tac Toe ({difficulty.capitalize()} Mode):")
        print(f"  Human Wins: {outcome['human']}")
        print(f"  AI Wins: {outcome['ai']}")
        print(f"  Draws: {outcome['draw']}")


if __name__ == "__main__":
    results = run_tests(games_per_difficulty=50)
    print_results(results)
